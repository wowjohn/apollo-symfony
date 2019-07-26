<?php
/**
 * Created by PhpStorm.
 * User: baofan
 * Date: 2019/7/23
 * Time: 13:24
 */

namespace Apollo;

use Exception;

class SAClient
{
    public function index()
    {
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
    }
}