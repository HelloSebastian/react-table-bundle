import React from 'react';
import {render} from 'react-dom';
import DefaultTable from "./ReactTable/DefaultTable";

require('react-table-6/react-table.css');
require('./css/styles.css');

const reactTables = document.getElementsByClassName('react-table-bundle');
for (let i = 0; i < reactTables.length; i++) {
    render(
        <DefaultTable
            table={JSON.parse(reactTables[i].dataset.table)}
        />, reactTables[i]
    );
}