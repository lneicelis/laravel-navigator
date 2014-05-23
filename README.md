laravel-navigator
=================

I just got tired of writing foreach in foreach in foreach :)
So I build simple package for Laravel 4.* to create any depth menu lists breadcrumbs etc.

## Installation

Require this package in your composer.json and run composer update:

    "luknei/navigator": "dev-master"

After updating composer, add the ServiceProvider to the providers array in app/config/app.php

    'Luknei\Navigator\NavigatorServiceProvider',

Add this to your facades in app.php:

     'Nav' => 'Luknei\Navigator\NavigatorFacade',

## Usage

### Adding items to navigator:

    //First level/depth items:
    //menu group - "menu", menu item - "auth", and variables of the template
    Nav::group('menu')->add('auth',[
        'title' => trans('auth::base.auth'),
        'href'  => '#',
        'icon'  => 'icon-key',
    ]);

    //Second level/depth items:
    //menu group - "menu", menu item - "auth.groups", and variables of the template
    Nav::group('menu')->add('auth.groups', [
        'title' => trans('auth::base.groups'),
        'href'  => URL::action('Auth\GroupsController@getAll'),
    ]);

### Setting a template

Template for the first level/depth items
Templates have a few element - @depth(), @foreach, @subgroup and variables you passed when adding element to the group
    @depth(1)
        <ul class="nav nav-list">
            @foreach
            <li>
                <a href="{{ $href }}" class="dropdown-toggle">
                    <i class="{{ $icon }}"></i>
                    <span class="menu-text"> {{ $title }} </span>
                </a>

                @subgroup
            </li>
            @endforeach
        </ul>
    @stop

You can Set the default template for any level/depth item

    @depth(default)
        <ul class="submenu" style="display: none;">
            @foreach
            <li>
                <a href="{{ $href }}">
                    <i class="icon-double-angle-right"></i>
                    {{ $title }}
                </a>

                @subgroup
            </li>
            @endforeach
        </ul>
    @stop

There are also options for even and odd depth/levels
    @depth(even)
    @depth(odd)

### Rendering the menu

To render your navigation menu simply set the group and use render method

    Nav::group('admin.menu')->render('admin.partials.sidemenu');

If you want to cache your rendered menu for more performance pass a second parameter - minutes to keep the cache

    Nav::group('admin.menu')->render('admin.partials.sidemenu', 20); //cached for 20min