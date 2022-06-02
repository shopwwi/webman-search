<?php
/**
 *-------------------------------------------------------------------------p*
 *
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2022 Shopwwi Inc. (http://www.shopwwi.com)
 *-------------------------------------------------------------------------c*
 * @license    http://www.shopwwi.com        s h o p w w i . c o m
 *-------------------------------------------------------------------------e*
 * @link       http://www.shopwwi.com by 象讯科技 phcent.com
 *-------------------------------------------------------------------------n*
 * @since      shopwwi象讯·PHP商城系统Pro
 *-------------------------------------------------------------------------t*
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
