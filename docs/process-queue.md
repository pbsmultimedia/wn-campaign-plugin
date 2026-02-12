# Process Queue

By default Winter CMS queue driver is sync, so jobs are processed immediately when dispatched.

To use the queue, you need to configure it in the config/queue.php file and set it to database.

To process the queue, you need to run the following command:

```bash
php artisan queue:work
```

This works, but it stops when the terminal is closed.

A more robust solution is to use a process manager like supervisor or systemd to run the command in the background.

[Using a system daemon](https://wintercms.com/docs/v1.2/docs/services/queues#using-a-system-daemon)

## Supervisor setup

After installing supervisor:

```bash
sudo apt-get update
sudo apt-get install supervisor
```

Create a supervisor config file:

```bash
sudo nano /etc/supervisor/conf.d/wintercms-queue.conf
```

Adapt the following content to the server setup:

```bash
[program:wintercms-queue]
directory=/var/www/html
process_name=%(program_name)s_%(process_num)02d
command=php artisan queue:work --sleep=3 --tries=3 --timeout=60
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/queue.log
user=www-data
```

Directory might be other, and full path to PHP might be needed. Check `which php` to get the full path.

Apply the new config:

```bash
sudo supervisorctl reread
sudo supervisorctl update
```

Start/stop the queue:

```bash
supervisorctl start wintercms-queue:*
supervisorctl stop wintercms-queue:*
```

Stop the queue:

```bash
supervisorctl stop wintercms-queue:*
```

Check the status of the queue:

```bash
supervisorctl status wintercms-queue:*
```

To debug:

tail -50 /var/log/supervisor/supervisord.log

