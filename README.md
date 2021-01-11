# ReactTableBundle

**This Bundle provides *simple* [react-table](https://github.com/tannerlinsley/react-table/tree/v6) 
configuration from your Doctrine Entities.** 

ReactTableBundle uses React Table v6. (React Table v7 is headless, means no CSS and layout. I'm not a CSS expert, 
so I'm still using v6 at the moment. Once a flex layout for v7 is ready, the bundle can be easily adapted, since the 
API for the columns is very similar.)


**The project is currently still under development. It can not be excluded that configuration changes.**

## Overview

1. [Features](#features)
2. [Installation](#installation)
3. [Your First Table](#your-first-table)
4. [Table Props Configuration](#table-props)
5. [Persistence Options Configuration](#persistence-options)


## Features

* Filtering*
* Sorting*
* Pagination*
* Persist table state (sorting, filtering, current page, ...)
* Column Types: TextColumn, BooleanColumn, DateTimeColumn, ActionColumn
* Helper for action buttons
* *all features from react-table***

*server-side

**not yet implemented

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

Then, enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

``` php
// config/bundles.php

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

#### Add Assets into your base.html.twig

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
    protected function buildColumns(ColumnBuilder $builder)
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
                'emptyData' => 'No Department',
                'filter' => array(SelectFilter::class, array(
                    'choices' => array(
                        'IT' => 'IT',
                        'Sales' => 'Sales'
                    )
                ))
            ))
            ->add('department.costCentre.name', TextColumn::class, array(
                'Header' => 'Cost Centre',
                'emptyData' => 'No Cost Centre',
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


### Step 2: In the Controller

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

### Step 3: Create your index.html.twig

``` html
{% extends 'base.html.twig' %}

{% block body %}
    <div class="react-table-bundle" data-table="{{ table }}"></div>
{% endblock %}
```

## Configuration


### Table Props

Table Props are provided directly to ReactTable and are a collection of setting options for the table.

#### Options

| Option                        | Type    | Default                  | 
|-------------------------------|---------|--------------------------|
| showPagination                | bool    | true                     |
| showPaginationTop             | bool    | false                    |
| showPaginationBottom          | bool    | true                     | 
| showPageSizeOptions           | bool    | true                     | 
| pageSizeOptions               | array   | [5, 10, 20, 25, 50, 100] |
| defaultPageSize               | int     | 20                       | 
| showPageJump                  | bool    | true                     | 
| collapseOnSortingChange       | bool    | true                     | 
| collapseOnPageChange          | bool    | true                     | 
| collapseOnDataChange          | bool    | true                     | 
| freezeWhenExpanded            | bool    | false                    | 
| sortable                      | bool    | true                     | 
| multiSort                     | bool    | true                     | 
| resizable                     | bool    | true                     | 
| filterable                    | bool    | true                     | 
| defaultSortDesc               | bool    | false                    | 
| className                     | string  | ''                       | 
| previousText                  | string  | 'Previous'               |
| nextText                      | string  | 'Next'                   | 
| loadingText                   | string  | 'Loading...'             |
| noDataText                    | string  | 'No rows found'          | 
| pageText                      | string  | 'Page'                   | 
| ofText                        | string  | 'of'                     | 
| rowsText                      | string  | 'Rows'                   | 
| pageJumpText                  | string  | 'jump to page'           |
| rowsSelectorText              | string  | 'rows per page'          | 


You can either perform settings for all tables via a YAML file or set each individual table

#### YAML-Configuration

```yaml
// config/packages/react_table.yaml

react_table:
    default_table_props:
        className: "-striped -highlight"
        sortable: false
```

#### PHP-Configuration

Inside from Table class:

``` php
// src/ReactTable/UserTable.php

class TestTable extends ReactTable
{
    ...

    public function configureTableProps(OptionsResolver $resolver)
    {
        parent::configureTableProps($resolver);
    
        $resolver->setDefaults(array(
            'defaultPageSize' => 10
        ));
    }
}
```


Outside from Table class:

``` php
// src/Controller/UserController.php

public function index(Request $request, ReactTableFactory $reactTableFactory) : Response
{
    $table = $reactTableFactory->create(UserTable::class);

    $table->setTableProps(array(
        'defaultPageSize' => 10
    ));

    ...
}
```


In the `configureTableProps` method, you can specify custom data that can be provided directly to the ReactTable.

### Persistence Options

#### Options

With the Persistence Options you can set which settings (filtering, sorting, current page, ...) should be stored in the cookies. By default, all of them are activated.

| Option         | Type    | Default  | 
|----------------|---------|----------|
| resized        | bool    | true     |
| filtered       | bool    | true     |
| sorted         | bool    | true     | 
| page           | bool    | true     | 
| page_size      | bool    | true     |

#### YAML-Configuration

```yaml
// config/packages/react_table.yaml
react_table:
    default_persistence_options:
        sorted: true
```


#### PHP-Configuration

Inside from Table class:

``` php
// src/ReactTable/UserTable.php

class TestTable extends ReactTable
{
    ...

    public function configurePersistenceOptions(OptionsResolver $resolver)
    {
        parent::configurePersistenceOptions($resolver);

        $resolver->setDefaults(array(
            'sorted' => false
        ));
    }
}
```


Outside from Table class:

``` php
// src/Controller/UserController.php

public function index(Request $request, ReactTableFactory $reactTableFactory) : Response
{
    $table = $reactTableFactory->create(UserTable::class);

    $table->setPersistenceOptions(array(
        'page' => true
    ));

    ...
}
```

## Columns

#### Default Options

| Option         | Type            | Default                       | 
|----------------|-----------------|-------------------------------|
| Header         | string          | ""                            |
| accessor       | string          | ""                            |
| width          | integer / null  | null                          | 
| filterable     | bool            | true                          |
| sortable       | bool            | true                          |
| show           | bool            | true                          |
| filter         | array / null    | [TextFilter::class, array()]  |
| emptyData      | string          | ""                            |
| sortQuery      | Closure / null  | null                          |
| dataCallback   | Closure / null  | null                          |


### TextColumn

Represents column width text.

#### Options



## ToDo's
* Documentation
* Tests
