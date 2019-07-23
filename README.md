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
         * symfony3 保存至 yml
         */
        $apolloClient->getIsModifyStatus() && $apolloClient->saveToYml();

//                /**
//                 * symfony4 保存至 .env
//                 */
//                $apolloClient->getIsModifyStatus() && $apolloClient->saveToEnv();
    } catch (Exception $exception) {
        echo($exception->getMessage());
    }
    
} while (true);
```

symfony 3 需安装 yaml 扩展 (Docker 方式)
```bash
RUN apt-get install libyaml-dev
```
```bash
RUN pecl install yaml && docker-php-ext-enable yaml
```