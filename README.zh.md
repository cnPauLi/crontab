php_crontab 
=============
[![Total Downloads](https://img.shields.io/packagist/dt/jenner/crontab.svg?style=flat)](https://packagist.org/packages/jenner/crontab)
[![Latest Stable Version](http://img.shields.io/packagist/v/jenner/crontab.svg?style=flat)](https://packagist.org/packages/jenner/crontab)
[![License](https://img.shields.io/packagist/l/jenner/crontab.svg?style=flat)](https://packagist.org/packages/jenner/crontab)

����pcntl��react/event-loop�Ķ�ʱ���������

[Ӣ��˵��](https://github.com/huyanping/php_crontab/blob/master/README.md "Ӣ��˵��")

Ϊʲôʹ��php_crontab��
------------
�������������Ķ�ʱ������Ҫ����ʱ��unix��crontab����ʱ�㹻�ġ���������зǳ���Ķ�ʱ����
��Ҫ����ʱ��������һЩ���⣬���磺
+ crontab����ͨ��һ���ı��ļ�����ʱ�������û��ע�ͣ���������˵ȥ��������ǱȽ��ѵġ�
+ �����ʱ�����ɢ���������ϣ���������Ҳ�ǱȽ��ѵġ�
+ ��������ռ����ǵ���־��ͬ������򵥡�
+ ��ͬ�û��Ķ�ʱ�����ɢ�ڲ�ͬ���ļ��С�
�������ϼ���ԭ��������Ҫһ������ͳһ�������õĶ�ʱ�����������

���ʹ��php_crontab��
---------------
�����ַ�ʽʹ��php_crontab������Ķ�ʱ����
�����дһ���ű���Ȼ��������뵽crontab�������У�ÿ����ִ��һ�Ρ�����`tests/simple`��
���������дһ���ػ����̽ű���������һ������һ��һֻ���У�ֱ����ɱ������
����ÿ���Ӽ��һ�ζ�ʱ��������`tests/daemon.php`

����
-----------
+ ��ʱ���������Ա��洢���κεط������磺mysql��redis�ȡ�
+ ��ʱ�������־���Ը��������Ҫ��������
+ ����û��Ķ�ʱ�������ͳһ����
+ ����̣�ÿ������һ������
+ �����Ϊÿ�����������û����û���
+ ��׼������Խ����ض���
+ ����react/event-loop����������Ϊһ���ػ���������
+ һ��HTTP�������������ͨ��������ʱ����

HTTP �ӿ�
-------------
HTTP ����: `GET`  
+ `add` ��������
+ `get_by_name` �����������ƻ�ȡ����
+ `remove_by_name` ������������ɾ������
+ `clear` ɾ����������
+ `get` ��ȡ��������
+ `start` ��ʼ��ⶨʱ����
+ `stop` ֹͣ��ⶨʱ����

ʾ��:
```shell
http://host:port/add?name=name&cmd=cmd&time=time&out=out&user=user&group=group&comment=comment
http://host:port/get_by_name?name=name
http://host:port/remove_by_name?name=name
http://host:port/clear
http://host:port/get
http://host:port/start
http://host:port/stop
```


**����crontab��������**
```shell
* * * * * php demo.php
```
```php
<?php
$missions = [
    [
        'name' => 'ls',
        'cmd' => "ls -al",
        'out' => '/tmp/php_crontab.log',
        'time' => '* * * * *',
        'user' => 'www',
        'group' => 'www'
    ],
    [
        'name' => 'hostname',
        'cmd' => "hostname",
        'out' => '/tmp/php_crontab.log',
        'time' => '* * * * *',
    ],
];

$tasks = array();
foreach($missions as $mission){
    $tasks[] = new \Jenner\Crontab\Mission($mission['name'], $mission['cmd'], $mission['time'], $mission['out']);
}

$crontab_server = new \Jenner\Crontab\Crontab(null, $tasks);
$crontab_server->start(time());
```
**��Ϊһ���ػ���������**

it will check the task configs every minute.
```php
$missions = [
    [
        'name' => 'ls',
        'cmd' => "ls -al",
        'out' => '/tmp/php_crontab.log',
        'time' => '* * * * *',
        'user' => 'www',
        'group' => 'www'
    ],
    [
        'name' => 'hostname',
        'cmd' => "hostname",
        'out' => '/tmp/php_crontab.log',
        'time' =>  '* * * * *',
    ],
];

$daemon = new \Jenner\Crontab\Daemon($missions);
$daemon->start();
```

**��Ϊ�ػ���������ͬʱ����һ��http server**
```php
$missions = [
    [
        'name' => 'ls',
        'cmd' => "ls -al",
        'out' => '/tmp/php_crontab.log',
        'time' => '* * * * *',
    ],
    [
        'name' => 'hostname',
        'cmd' => "hostname",
        'out' => '/tmp/php_crontab.log',
        'time' => '* * * * *',
    ],
];

$http_daemon = new \Jenner\Crontab\HttpDaemon($missions, "php_crontab.log");
$http_daemon->start($port = 6364);
```
Then you can manage the crontab task by curl like:
```shell
curl http://127.0.0.1:6364/get_by_name?name=ls
curl http://127.0.0.1:6364/remove_by_name?name=hostname
curl http://127.0.0.1:6364/get
```

**�����ű�**
```shell
[root@jenner php_crontab]# ./bin/php_crontab 
php_crontab help:
-c  --config    crontab tasks config file
-p  --port      http server port
-f  --pid-file  daemon pid file
-l  --log       crontab log file
[root@jenner php_crontab]#nohup ./bin/php_crontab -c xxoo.php -p 8080 -f /var/php_crontab.pid -l /var/logs/php_crontab.log >/dev/null & 
```

[blog:www.huyanping.cn](http://www.huyanping.cn/ "����Գʼ�ղ���")



