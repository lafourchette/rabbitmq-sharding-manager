# Install

```
curl -LO "https://github.com/lafourchette/rabbitmq-sharding-manager/releases/download/v1.0.0/rabbitmq-sharding-manager.phar"
php rabbitmq-sharding-manager.phar
```

# Usage

Create a vhost with a sharding of 10 queues
```
php rabbitmq-manager.phar vhost:configure -uhttp://guest:guest@localhost:15672 routing_key.{modulus} 10
```

Drop it
```
php rabbitmq-manager.phar vhost:drop -uhttp://guest:guest@localhost:15672 tmp_vhost

```
