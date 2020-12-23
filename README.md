# ReactTableBundle

**This Bundle provides *simple* [react-table](https://github.com/tannerlinsley/react-table/tree/v6) 
configuration from your Doctrine Entities.** 

ReactTableBundle uses React Table v6. (React Table v7 is headless, means no CSS and layout. I'm not a CSS expert, 
so I'm still using v6 at the moment. Once a flex layout for v7 is ready, the bundle can be easily adapted, since the 
API for the columns is very similar.)


**The project is currently still under development. It can not be excluded that configuration changes.**

## Overview

1. [Features](#features)
1. [Installation](#installation)
2. [Your First Table](#your-first-table)


## Features

* Filtering*
* Sorting*
* Pagination*
* Persist table state
* Column Types: TextColumn, BooleanColumn, DateTimeColumn, ActionColumn
* Helper for action buttons
* All features from react-table

*server-side

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download this bundle:

``` bash
$ composer require hello-sebastian/react-table-bundle
```

If you're using Symfony Flex - you're done! Symfony Flex will create default
configuration for you, change it if needed. If you don't use Symfony Flex, you will need to do
a few more simple steps.

### Step 2: Enable the Bundle (without flex)

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

``` php
# config/bundles.php
return [
    // ...
    HelloSebastian\ReactTableBundle\ReactTableBundle::class => ['all' => true],
];
```


### Step 3: Assetic Configuration

#### Install the web assets

``` bash
# if possible, make absolute symlinks (best practice) in web/ if not, make a hard copy

$ php bin/console assets:install --symlink
```

``` bash
# make a hard copy of assets in web/

$ php bin/console assets:install
```

### Step 4: Add Assets into your base.html.twig

``` html
<link rel="stylesheet" href="{{ asset('bundles/reacttable/app.css') }}">
<script src="{{ asset('bundles/reacttable/app.js') }}"></script>
```


## Your First Table

### Step 1: Create a React Table class


``` php
// src/ReactTable/UserTable.php

<?php

namespace App\ReactTable;

use App\Entity\User;
use HelloSebastian\ReactTableBundle\Columns\ActionColumn;
use HelloSebastian\ReactTableBundle\Columns\BooleanColumn;
use HelloSebastian\ReactTableBundle\Columns\ColumnBuilder;
use HelloSebastian\ReactTableBundle\Columns\DateTimeColumn;
use HelloSebastian\ReactTableBundle\Columns\TextColumn;
use HelloSebastian\ReactTableBundle\Data\ActionButton;
use HelloSebastian\ReactTableBundle\Filter\SelectFilter;
use HelloSebastian\ReactTableBundle\ReactTable;

class UserTable extends ReactTable
{
    /**
     * @inheritDoc
     */
    protected function buildColumns(ColumnBuilder $columnBuilder)
    {
        $columnBuilder
            ->add('username', TextColumn::class, array(
                'Header' => 'Username'
            ))
            ->add('email', TextColumn::class, array(
                'Header' => 'E-Mail',
                'show' => false
            ))
            ->add('firstName', TextColumn::class, array(
                'Header' => 'First name'
            ))
            ->add('lastName', TextColumn::class, array(
                'Header' => 'Last name'
            ))
            ->add('createdAt', DateTimeColumn::class, array(
                'Header' => 'Created at',
                'format' => 'd.m.Y'
            ))
            ->add('department.name', TextColumn::class, array(
                'Header' => 'Department',
                '_emptyData' => 'No Department',
                'filter' => array(SelectFilter::class, array(
                    'choices' => array(
                        'IT' => 'IT',
                        'Sales' => 'Sales'
                    )
                ))
            ))
            ->add('department.costCentre.name', TextColumn::class, array(
                'Header' => 'Cost Centre',
                '_emptyData' => 'No Cost Centre',
                'filter' => array(SelectFilter::class, array(
                    'choices' => array(
                        '001' => '001',
                        '002' => '002'
                    )
                ))
            ))
            ->add('isActive', BooleanColumn::class, array(
                'Header' => 'is active',
                'trueValue' => 'yes'
            ))
            ->add(null, ActionColumn::class, array(
                'Header' => 'Actions',
                'width' => 120,
                'buttons' => array(
                    ActionButton::create("show_user", "show", "btn-success"),
                    ActionButton::create("edit_user", "edit", "btn-warning")
                )
            ));
    }

    protected function getEntityClass(): string
    {
        return User::class;
    }
}
```


### Step 3: In the Controller

``` php
// src/Controller/UserController.php

// ...
use HelloSebastian\ReactTableBundle\ReactTableFactory;
// ...

/**
 * @Route("/", name="default")
 */
public function index(Request $request, ReactTableFactory $reactTableFactory) : Response
{
    $table = $reactTableFactory->create(UserTable::class);

    $table->handleRequest($request);
    if ($table->isCallback()) {
        return $table->getResponse();
    }

    return $this->render('index.html.twig', array(
        'table' => $table->createView()
    ));
}
```

### Step 4: Create your index.html.twig

``` html
{% extends 'base.html.twig' %}

{% block body %}
    <div class="react-table-bundle" data-table="{{ table }}"></div>
{% endblock %}
```

## ToDo's
* Documentation
* Tests
