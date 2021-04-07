```bash
# PHP Extensions
sudo apt install php-amqp
sudo apt install php-sqlite3

# RABBIT MQ
docker run --rm -it --hostname my-rabbit -p 15673:15672 -p 5673:5672 rabbitmq:3-management
```
