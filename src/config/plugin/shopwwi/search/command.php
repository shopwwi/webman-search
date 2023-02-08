<?php
/**
 *-------------------------------------------------------------------------s*
 *
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2022 Shopwwi Inc. (http://www.shopwwi.com)
 *-------------------------------------------------------------------------o*
 * @license    http://www.shopwwi.com        s h o p w w i . c o m
 *-------------------------------------------------------------------------p*
 * @link       http://www.shopwwi.com by 无锡豚豹科技 shopwwi.com
 *-------------------------------------------------------------------------w*
 * @since      shopwwi象讯·PHP商城系统Pro
 *-------------------------------------------------------------------------w*
* author tycoonSong 8988354@qq.com
 *-------------------------------------------------------------------------i*
 */


use Shopwwi\WebmanSearch\Command\SearchCreateIndexCommand;
use Shopwwi\WebmanSearch\Command\SearchDropIndexCommand;
use Shopwwi\WebmanSearch\Command\SearchUpdateIndexCommand;
use Shopwwi\WebmanSearch\Command\ShopwwiSearchCommand;

return [
    SearchCreateIndexCommand::class,
    SearchDropIndexCommand::class,
    SearchUpdateIndexCommand::class,
    ShopwwiSearchCommand::class
];
