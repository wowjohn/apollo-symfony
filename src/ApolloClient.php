<?php
/**
 * Created by PhpStorm.
 * User: baofan
 * Date: 2019/7/11
 * Time: 17:35
 */

namespace Apollo;

use GuzzleHttp\Client;
use DirectoryIterator;
use Directory;

class ApolloClient
{
    /**
     * @desc Apollo配置服务的地址
     */
    protected $configServerUrl;

    /**
     * @desc 应用的appId
     */
    protected $appId;

    /**
     * @desc  集群名
     */
    protected $clusterName = 'default';

    /**
     * @desc  Namespace的名字
     */
    protected $namespaceNames;

    /**
     * @desc  应用部署的机器ip
     */
    protected $ip = '127.0.0.1';

    /**
     * @desc  上一次的releaseKey
     */
    protected $releaseKey;

    /**
     * @desc 根目录
     */
    protected $rootPath;

    /**
     * @var bool
     *
     * @desc 更新状态
     */
    private $modifyStatus = false;

    public function __construct()
    {
        $this->rootPath = preg_replace('/(vendor[\s\S]*)/', '', __DIR__);
    }

    /**
     * noCacheRsync 不带缓存的Http接口
     *
     * @author baofan
     */
    public function noCacheRsync()
    {
        $namespaceNameArray = explode(',', $this->namespaceNames);
        foreach ($namespaceNameArray as $namespaceName) {
            $this->setReleaseKey($namespaceName);

            $queryArray = [
                'releaseKey' => $this->releaseKey,
                'ip'         => $this->ip,
            ];

            $baseUri = "{$this->configServerUrl}/configs/{$this->appId}/{$this->clusterName}/{$namespaceName}";

            $requestUri = $baseUri . '?' . http_build_query($queryArray);

            $resultArray = $this->send($requestUri);
            if (empty($resultArray)) {
                continue;
            }

            $this->modifyStatus = true;

            $resArray = var_export($resultArray, true);

            $content = <<<EOF
<?php

return {$resArray};
EOF;
            file_put_contents($this->getSaveConfigFile($namespaceName), $content);
        }
    }

    /**
     * saveToEnv 保存至 .env
     *
     * @author baofan
     */
    public function saveToEnv()
    {
        $dirs = new DirectoryIterator($this->rootPath . '/apollo');

        $envArray = [];
        /** @var Directory $dirInfo */
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->getExtension() !== 'php') {
                continue;
            }

            $configArray = include $dirInfo->getPathname();

            $envArray = array_merge($envArray, $configArray['configurations']);
        }

        if ($envArray) {
            $fileEnv = $this->rootPath . DIRECTORY_SEPARATOR . '.env';

            file_put_contents($fileEnv, '');

            usleep(100);

            foreach ($envArray as $key => $value) {
                $key = strtoupper($key);
                file_put_contents($fileEnv, "{$key}={$value}" . PHP_EOL, FILE_APPEND);
            }

            $this->modifyStatus = false;
        }
    }

    /**
     * getSaveConfigFile 获取保存文件 path
     *
     * @param $namespaceName
     *
     * @return string
     * @author baofan
     */
    private function getSaveConfigFile($namespaceName)
    {
        return "{$this->rootPath}/apollo/apolloConfig.{$namespaceName}.php";
    }

    /**
     * setReleaseKey 设置 releaseKey
     *
     * @param $namespaceName
     *
     * @author baofan
     */
    private function setReleaseKey($namespaceName)
    {
        $file = $this->rootPath . '/apollo/' . "apolloConfig.{$namespaceName}.php";

        if (file_exists($file)) {
            $configArray = include $file;

            is_array($configArray) && $this->releaseKey = $configArray['releaseKey'] ?? '';
        };
    }

    /**
     * send 请求 配置接口
     *
     * @param $requestUri
     *
     * @return array
     * @author baofan
     */
    private function send($requestUri)
    {
        $client = new Client([
            'timeout' => 60,
        ]);

        $res = $client->get($requestUri);

        if ($res->getStatusCode() !== 200) {
            return [];
        }

        $resContent = json_decode($res->getBody()->getContents(), true);

        return (array)$resContent;
    }

    /**
     * setConfigServerUrl
     *
     * @param $configServerUrl
     *
     * @return ApolloClient
     */
    public function setConfigServerUrl($configServerUrl)
    {
        $this->configServerUrl = $configServerUrl;

        return $this;
    }

    /**
     * ApolloClient  AppId
     *
     * @param $appId
     *
     * @return ApolloClient
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * ApolloClient setClusterName
     *
     * @param $clusterName
     *
     * @return ApolloClient
     */
    public function setClusterName($clusterName)
    {
        $this->clusterName = $clusterName;

        return $this;
    }

    /**
     * ApolloClient setNamespaceNames
     *
     * @param $namespaceNames
     *
     * @return ApolloClient
     */
    public function setNamespaceNames($namespaceNames)
    {
        $this->namespaceNames = $namespaceNames;

        return $this;
    }

    /**
     * @param string $ip
     *
     * @return ApolloClient
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * ApolloClient  ModifyStatus
     *
     * @return bool
     */
    public function getIsModifyStatus()
    {
        return $this->modifyStatus;
    }

    /**
     * @param bool $modifyStatus
     *
     * @return ApolloClient
     */
    public function setModifyStatus(bool $modifyStatus): ApolloClient
    {
        $this->modifyStatus = $modifyStatus;

        return $this;
    }
}