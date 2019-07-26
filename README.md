# apollo-symfony
symfony 引入阿波罗配置中心


> symfony 使用方法
```php
$apolloClient = new ApolloClient();

$apolloClient
    ->setConfigServerUrl(getenv('CONFIG_SERVER'))
    ->setAppId(getenv('APPID'))
    ->setNamespaceNames(getenv('NAMESPACES'));

do {
    try {
        $apolloClient->noCacheRsync();

        /**
        * symfony4 保存至 .env
        */
        $apolloClient->getIsModifyStatus() && $apolloClient->saveToEnv();
    } catch (Exception $exception) {
        echo($exception->getMessage());
    }
    
} while (true);
```