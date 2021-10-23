<?php /** @noinspection PhpInternalEntityUsedInspection */

/*
 *
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the MIT License. see <https://opensource.org/licenses/MIT>.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://opensource.org/licenses/MIT MIT License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace kim\present\polyfill\runtimeid;

use kim\present\lib\accessor\Accessor;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\Server;

use function file_get_contents;
use function json_decode;

final class RuntimeIdPolyfill{
    private function __construct(){ }

    /**
     * Force registration of the missing runtime ID.
     *
     * if the data or Minecraft is updated, this may require modification.
     * Also, this is not the correct way to do this, as it is forcibly added using reflection classes.
     *
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function run() : bool{
        $nameToLegacyMap = json_decode(file_get_contents(Server::getInstance()->getResourcePath() . "vanilla/block_id_map.json"), true);
        $metaMap = [];

        $runtimeBlockMapping = Accessor::from(RuntimeBlockMapping::class);
        /** @see RuntimeBlockMapping::getBedrockKnownStates() */
        foreach($runtimeBlockMapping->getBedrockKnownStates() as $runtimeId => $state){
            $name = $state->getString("name");
            if(!isset($nameToLegacyMap[$name]))
                continue;

            $legacyId = $nameToLegacyMap[$name];
            if(!isset($metaMap[$legacyId])){
                $metaMap[$legacyId] = 0;
            }

            $meta = $metaMap[$legacyId]++;
            if($meta > 0xf)
                continue;

            /** @see RuntimeBlockMapping::registerMapping() */
            $runtimeBlockMapping->registerMapping($runtimeId, $legacyId, $meta);
        }
        return true;
    }
}