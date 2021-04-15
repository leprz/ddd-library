docker run --rm -it --hostname my-rabbit -p 15673:15672 -p 5673:5672 rabbitmq:3-management
php packages/circulation/bin/console messenger:consume retry -vv &
php packages/circulation/bin/console messenger:consume async -vv &
php packages/finances/bin/console messenger:consume async -vv
