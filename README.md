# apollo-symfony
symfony 引入阿波罗配置中心


> 使用方法
```php
$apollo = new ApolloClient();

do {
    try {
        $apollo
            ->setConfigServerUrl(getenv('CONFIG_SERVER'))
            ->setAppId(getenv('APPID'))
            ->setNamespaceNames(getenv('NAMESPACES'))
            ->noCacheRsync();
    } catch (\Exception $exception) {
        $output->writeln($exception->getMessage());
    }
} while (true);
```