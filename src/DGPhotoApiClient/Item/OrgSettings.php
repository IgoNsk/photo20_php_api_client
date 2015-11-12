<?php

namespace DG\API\Photo\Item;

class OrgSettings
{
    /**
     * @var array
     */
    private $providers = [];

    /**
     * @param string $providerCode
     * @param boolean $isVisible
     */
    public function addProviderConfig($providerCode, $isVisible)
    {
        $this->providers[$providerCode] = [
            'code' => $providerCode,
            'is_visible' => $isVisible
        ];
    }

    /**
     * @return array
     */
    public function getHiddenProvidersCodes()
    {
        $hiddenProviders = array_filter($this->providers, function ($provider) {
            return !$provider['is_visible'];
        });

        return array_map(function ($item) {
            return $item['code'];
        }, $hiddenProviders);
    }

    /**
     * @return array
     */
    public function getProvidersCodes()
    {
        $codes = array_map(function ($item) {
            return $item['code'];
        }, $this->providers);
        return array_values($codes);
    }

    /**
     * @param string $providerCode
     */
    public function removeProviderConfig($providerCode)
    {
        if (isset($this->providers[$providerCode])) {
            unset($this->providers[$providerCode]);
        }
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            'providers' => $this->providers
        ];
    }
}
