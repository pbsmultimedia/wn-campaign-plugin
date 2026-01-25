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
