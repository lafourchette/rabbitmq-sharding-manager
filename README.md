# Install

```
curl -LO "https://github.com/openl10n/openl10n-cli/releases/download/${VERSION}/openl10n.phar"
chmod +x openl10n.phar
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
