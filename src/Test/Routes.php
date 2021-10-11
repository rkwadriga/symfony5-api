<?php declare(strict_types=1);
/**
 * Created 2021-10-11
 * Author Dmitry Kushneriov
 */

namespace App\Test;

class Routes
{
    public const URL_LOGIN = 'app_login';
    public const URL_LOGOUT = 'app_logout';
    public const URL_GET_USER = 'api_users_get_item';
    public const URL_GET_USERS = 'api_users_get_collection';
    public const URL_CREATE_USER = 'api_users_post_collection';
    public const URL_UPDATE_USER = 'api_users_put_item';
    public const URL_GET_CHEESE_LISTINGS = 'api_cheeses_get_collection';
    public const URL_GET_CHEESE_LISTING = 'api_cheeses_get_item';
    public const URL_CREATE_CHEESE_LISTING = 'api_cheeses_post_collection';
    public const URL_UPDATE_CHEESE_LISTING = 'api_cheeses_put_item';
}