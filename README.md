# ReactTableBundle

**This Bundle provides *simple* [react-table](https://github.com/tannerlinsley/react-table/tree/v6) configuration for your Doctrine Entities.** With the option to create your own columns in JavaScript.

ReactTableBundle uses React Table v6. (React Table v7 is headless, means no CSS and layout. I'm not a CSS expert, 
so I'm still using v6 at the moment. Once a flex layout for v7 is ready, the bundle can be easily adapted, since the 
API for the columns is very similar.)

Highly inspired by [SgDatatablesBundle](https://github.com/stwe/DatatablesBundle).

**The project is currently still under development. It can not be excluded that configuration changes.**



## When should I not use ReactTableBundle

ReactTableBundle is designed for simple tables that are strongly bound to the entities. If you are creating highly customized tables with many components and a lot of client-side programming, ReactTableBundle is not suitable for that. However, you can of course use ReactTableBundle alongside your complex tables.



## Overview

1. [Features](#features)
2. [Installation](#installation)
3. [Your First Table](#your-first-table)
4. [Columns](#columns)
5. [Table Props Configuration](#table-props)
6. [Persistence Options Configuration](#persistence-options)


## Features

* Table Configuration in PHP
* Filtering*
* Sorting*
* Pagination*
* Persist table state (sorting, filtering, current page, ...) in cookies
* Column Types: [TextColumn](#textcolumn), [BooleanColumn](#booleancolumn), [DateTimeColumn](#datetimecolumn), [ActionColumn](#actioncolumn)
* Custom Columns with [OwnColumn](#owncolumn)

*server-side

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download this bundle:

``` bash
$ composer require hello-sebastian/react-table-bundle
```



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
# if possible, make absolute symlinks (best practice) in public/ if not, make a hard copy

$ php bin/console assets:install --symlink
```

``` bash
# make a hard copy of assets in public/

$ php bin/console assets:install
```

#### Add Assets into your base.html.twig

``` html
<link rel="stylesheet" href="{{ asset('bundles/reacttable/app.css') }}">
<script src="{{ asset('bundles/reacttable/app.js') }}"></script>
```



## Your First Table

### Step 1: Create a ReactTable class


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
                        '002' => '002',
                      	'null' => 'empty'
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
                    array(
                        'displayName' => 'show',
                        'routeName' => 'show_user',
                        'additionalClassNames' => 'btn-success'
                    ),
                    array(
                        'displayName' => 'edit',
                        'routeName' => 'edit_user'
                    )
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

### Step 3: Add table in Template

``` html
{% extends 'base.html.twig' %}

{% block body %}
    <div class="react-table-bundle" data-table="{{ table }}"></div>
{% endblock %}
```



## Columns

### TextColumn

Represents column with text.

#### Options

| Option          | Type           | Default                      | Description                                                  |
| --------------- | -------------- | ---------------------------- | ------------------------------------------------------------ |
| Header          | string         | ""                           | set colum header                                             |
| width           | integer / null | null                         | width in px for column                                       |
| filterable      | bool           | true                         | enable / disable filtering for this column                   |
| sortable        | bool           | true                         | enable / disable sortable for this column                    |
| resizable       | bool           | true                         | enable / disable resizable for this column                   |
| show            | bool           | true                         | show / hide column                                           |
| className       | string         | ""                           | set the classname of the `td` element of the column          |
| headerClassName | string         | ""                           | set the classname of the `th` element of the column          |
| footerClassName | string         | ""                           | set the classname of the `td` element of the column's footer |
| filter          | array / null   | [TextFilter::class, array()] | first element in array is a filter class, second element is a configuration array for the filter class (see Filters) |
| emptyData       | string         | ""                           | default value if attribute from entity is null               |
| sortQuery       | Closure / null | null                         | custom sort query                                            |
| dataCallback    | Closure / null | null                         | custom data callback                                         |

#### Example

```php
->add('username', TextColumn::class, array(
    'Header' => 'Username',
  	'emptyData' => "No Username found.",
  
    //optional overrides ...
  	'dataCallback' => function (User $user) { //entity class from getEntityClass
        //you can return what ever you want ... but only string or number  
        return $user->getId() . " " . $user->getUsername();
    },
  	'sortQuery' => function (QueryBuilder $qb, $direction) {
        $qb->addOrderBy('username', $direction);
    },
  	'filter' => array(TextFilter::class, array(
    	'placeholder' => 'Search ...',
      	'filterQuery' => function (QueryBuilder $qb, $field, $value) {
            //add custom expressions to QueryBuilder ...
          	//field = "username"
          	//value = text from filter
        }
    ))
))
```



### BooleanColumn

Represents column with boolean values.

#### Options

All options of TextColumn

The option `filter` is set to `SelectFilter` by default.

**And**:

| Option     | Type   | Default | Description            |
| ---------- | ------ | ------- | ---------------------- |
| trueLabel  | string | "True"  | label for true values  |
| falseLabel | string | "False" | label for false values |

#### Example

```php
->add('isActive', BooleanColumn::class, array(
    'Header' => 'is active',
    'trueLabel' => 'yes',
    'falseLabel' => 'no'
))
```



### DateTimeColumn

Represents column with DateType values.

#### Options

All Options of TextColumn

**And:**

| Option | Type   | Default       | Description            |
| ------ | ------ | ------------- | ---------------------- |
| format | string | "Y-m-d H:i:s" | DateTime format string |

#### Example

```php
->add('createdAt', DateTimeColumn::class, array(
    'Header' => 'Created at',
    'format' => 'd.m.Y'
))
```



### OwnColumn

With OwnColumn you can provided custom data und JavaScript configuration to the table. E.g. you can create a Image or Link column with OwnColumn.

#### Options

All Options of TextColumn.

`sortable` and `filterable` are disabled by default. If you want to enable that you must provide custom `sortQuery` and `filter` with `filterQuery`.

`dataCallback`  is required.

#### Example

**Table configuration in PHP**

```php
->add("custom", OwnColumn::class, array(
    'Header' => 'My Column',
    'dataCallback' => function (User $user) {
        return "Hello";
    },
    'sortQuery' => function (QueryBuilder $qb, $direction) {
      	
    }
))
```

**Extend table configuration in JavaScript**

In your `base.html.twig` **before**  you include the bundle JS file you can listen to the custom event `rtb:componentDidMount`. Inside the event you can access the array of JavaScript column objects.

```javascript

document.addEventListener("rtb:componentDidMount", function (e) {
  e.detail.persistenceOptions.filtered = false; //see Persistence Options

  //loop over all columns and filter by type "own". If you have multiply OwnColumns you must extend the filtering
  
  //Then you can set and access all provided attributes by react-table. E.g. React Cell, Footer, Header, Filter - components ...
  
  e.detail.columns.forEach(col => {
    if (col.type === "own") {
      col.Header = "Custom Column";
      col.show = true;

      col.Cell = (row) => { //row is provided by react-table
        //if you import React you can render a customer component here as well.
        // return <MyCell row={row} />;
        return row.original.username;
      }
    }
  });
});
        
```



### ActionColumn

Represents column for action buttons (show / edit / remove ...).

#### Options

All Options of TextColumn

`sortable` and `filterable` are disable by default.

**And:**

| Option  | Type  | Default | Description                              |
| ------- | ----- | ------- | ---------------------------------------- |
| buttons | array | []      | array of buttons configuration as array. |

#### Example

```php
->add(null, ActionColumn::class, array(
    'Header' => 'Actions',
    'width' => 120, //optional
    'buttons' => array(
        array(
            'displayName' => 'show',
            'routeName' => 'show_user',
            'additionalClassNames' => 'btn-success'
        ),
        array(
            'displayName' => 'edit',
            'routeName' => 'edit_user',
            'additionalClassNames' => 'btn-success'
       )
  	)
))
```

#### ActionButtons

| Option               | Type   | Default     | Description                                                  |
| -------------------- | ------ | ----------- | ------------------------------------------------------------ |
| displayName          | string | ""          | label of button in table                                     |
| routeName            | string | ""          | route name                                                   |
| routeParams          | array  | array("id") | Array of property value names for the route parameters. By default is `id` set. |
| classNames           | string | ""          | CSS class names which added directly to the `a` element. Overrides default class names from YAML config. |
| additionalClassNames | string | ""          | You can set default class names in YAML config. Then you can add additional class names to the button without override the default config. |

**YAML Config**

```yaml
# config/packages/react_table.yaml

react_table:
	# other configuration ...
	default_column_options:
        action_column:
            default_class_names: 'btn btn-xs'
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

class UserTable extends ReactTable
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

class UserTable extends ReactTable
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



## ToDo's
* Documentation
  * More Examples
* Twig extension to render `div` element
* Tests
  * Unit Tests
