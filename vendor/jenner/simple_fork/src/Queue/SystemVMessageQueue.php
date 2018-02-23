<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 15:15
 */

namespace Jenner\SimpleFork\Queue;


class SystemVMessageQueue implements QueueInterface
{
    /**
     * ��Ϣ�������ͣ����ڽ�һ����Ϣ�����е���Ϣ���з���
     * @var int
     */
    protected $msg_type;

    /**
     * ���б�־
     * @var
     */
    protected $queue;

    /**
     * �Ƿ����л�
     * @var bool
     */
    protected $serialize_needed;

    /**
     * �޷�д�����ʱ���Ƿ�����
     * @var bool
     */
    protected $block_send;

    /**
     * ����λMSG_IPC_NOWAIT������޷���ȡ��һ����Ϣ���򲻵ȴ����������λNULL�����ȴ���Ϣ����
     * @var int
     */
    protected $option_receive;

    /**
     * ϣ�����յ��������Ϣ��С
     * @var int
     */
    protected $maxsize;

    /**
     * IPCͨ��KEY
     * @var
     */
    protected $key_t;

    protected $ipc_filename;

    /**
     * @param int $channel ��Ϣ����
     * @param string $ipc_filename IPCͨ�ű�־�ļ������ڻ�ȡΨһIPC KEY
     * @param bool $serialize_needed �Ƿ����л�
     * @param bool $block_send �޷�д�����ʱ���Ƿ�����
     * @param int $option_receive ����λMSG_IPC_NOWAIT������޷���ȡ��һ����Ϣ���򲻵ȴ����������λNULL�����ȴ���Ϣ����
     * @param int $maxsize ϣ�����յ��������Ϣ
     */
    public function __construct(
        $channel = 1,
        $ipc_filename = __FILE__,
        $serialize_needed = true,
        $block_send = true,
        $option_receive = MSG_IPC_NOWAIT,
        $maxsize = 100000
    )
    {
        $this->ipc_filename = $ipc_filename;
        $this->msg_type = $channel;
        $this->serialize_needed = $serialize_needed;
        $this->block_send = $block_send;
        $this->option_receive = $option_receive;
        $this->maxsize = $maxsize;
        $this->initQueue($ipc_filename, $channel);
    }

    /**
     * ��ʼ��һ������
     * @param $ipc_filename
     * @param $msg_type
     * @throws \Exception
     */
    protected function initQueue($ipc_filename, $msg_type)
    {
        $this->key_t = $this->getIpcKey($ipc_filename, $msg_type);
        $this->queue = \msg_get_queue($this->key_t);
        if (!$this->queue) throw new \RuntimeException('msg_get_queue failed');
    }

    /**
     * @param $ipc_filename
     * @param $msg_type
     * @throws \Exception
     * @return int
     */
    public function getIpcKey($ipc_filename, $msg_type)
    {
        if (!file_exists($ipc_filename)) {
            $create_file = touch($ipc_filename);
            if ($create_file === false) {
                $message = "ipc_file is not exists and create failed";
                throw new \RuntimeException($message);
            }
        }

        $key_t = \ftok($ipc_filename, $msg_type);
        if ($key_t == 0) throw new \RuntimeException('ftok error');

        return $key_t;
    }

    /**
     * �Ӷ��л�ȡһ��
     * @param $channel
     * @return bool
     * @throws \Exception
     */
    public function get($channel)
    {
        $this->msg_type = $channel;
        $queue_status = $this->status();
        if ($queue_status['msg_qnum'] > 0) {
            if (\msg_receive(
                    $this->queue,
                    $this->msg_type,
                    $msgtype_erhalten,
                    $this->maxsize, $data,
                    $this->serialize_needed,
                    $this->option_receive,
                    $err
                ) === true
            ) {
                return $data;
            } else {
                throw new \RuntimeException($err);
            }
        } else {
            return false;
        }
    }

    /**
     * д�����
     * @param $channel
     * @param $message
     * @return bool
     * @throws \Exception
     */
    public function put($channel, $message)
    {
        $this->msg_type = $channel;
        if (!\msg_send($this->queue, $this->msg_type, $message, $this->serialize_needed, $this->block_send, $err) === true) {
            throw new \RuntimeException($err);
        }

        return true;
    }

    /*
     * ����ֵ�����±����£�
     * msg_perm.uid	 The uid of the owner of the queue. �û�ID
     * msg_perm.gid	 The gid of the owner of the queue. �û���ID
     * msg_perm.mode	 The file access mode of the queue. ����ģʽ
     * msg_stime	 The time that the last message was sent to the queue. ���һ�ζ���д��ʱ��
     * msg_rtime	 The time that the last message was received from the queue.  ���һ�ζ��н���ʱ��
     * msg_ctime	 The time that the queue was last changed. ���һ���޸�ʱ��
     * msg_qnum	 The number of messages waiting to be read from the queue. ��ǰ�ȴ�����ȡ�Ķ�������
     * msg_qbytes	 The maximum number of bytes allowed in one message queue.  һ����Ϣ������������յ������Ϣ�ܴ�С
     *               On Linux, this value may be read and modified via /proc/sys/kernel/msgmnb.
     * msg_lspid	 The pid of the process that sent the last message to the queue. �������Ϣ�Ľ���ID
     * msg_lrpid	 The pid of the process that received the last message from the queue. ��������Ϣ�Ľ���ID
     *
     * @return array
     */
    /**
     * @return array
     */
    public function status()
    {
        $queue_status = \msg_stat_queue($this->queue);
        return $queue_status;
    }

    /**
     * ��ȡ���е�ǰ�ѻ�״̬
     * @param $channel
     * @return mixed
     */
    public function size($channel)
    {
        $this->msg_type = $channel;
        $status = $this->status();

        return $status['msg_qnum'];
    }

    /**
     * allows you to change the values of the msg_perm.uid,
     * msg_perm.gid, msg_perm.mode and msg_qbytes fields of the underlying message queue data structure
     * ���������޸Ķ������н��յ�����ȡ������
     *
     * @param string $key ״̬�±�
     * @param int $value ״ֵ̬
     * @return bool
     */
    public function setStatus($key, $value)
    {
        $this->checkSetPrivilege($key);
        if ($key == 'msg_qbytes')
            return $this->setMaxQueueSize($value);
        $queue_status[$key] = $value;

        return \msg_set_queue($this->queue, $queue_status);
    }

    /**
     * ɾ��һ������
     * @return bool
     */
    public function remove()
    {
        return \msg_remove_queue($this->queue);
    }

    /**
     * �޸Ķ��������ɵ�����ֽ�������ҪrootȨ��
     * @param $size
     * @throws \Exception
     * @return bool
     */
    public function setMaxQueueSize($size)
    {
        $user = \get_current_user();
        if ($user !== 'root')
            throw new \Exception('changing msg_qbytes needs root privileges');

        return $this->setStatus('msg_qbytes', $size);
    }

    /**
     * �ж�һ�������Ƿ����
     * @param $key
     * @return bool
     */
    public function queueExists($key)
    {
        return \msg_queue_exists($key);
    }

    /**
     * ����޸Ķ���״̬��Ȩ��
     * @param $key
     * @throws \Exception
     */
    private function checkSetPrivilege($key)
    {
        $privilege_field = array('msg_perm.uid', 'msg_perm.gid', 'msg_perm.mode');
        if (!\in_array($key, $privilege_field)) {
            $message = 'you can only change msg_perm.uid, msg_perm.gid, " .
            " msg_perm.mode and msg_qbytes. And msg_qbytes needs root privileges';

            throw new \RuntimeException($message);
        }
    }

    /**
     * init when wakeup
     */
    public function __wakeup()
    {
        $this->initQueue($this->ipc_filename, $this->msg_type);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this);
    }
}