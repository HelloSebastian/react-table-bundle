import React from 'react';

const column = (config) => {

    const {buttons} = config;
    delete config.buttons;

    return {
        ...config,
        filterable: false,
        sortable: false,
        Cell: row => (
            <div>
                {buttons.map((button, key) => (
                    <a
                        key={key}
                        href={row.original[config.accessor]['route_' + key]}
                        className={button.classNames}
                        style={{marginRight: '5px'}}
                    >{button.name}</a>
                ))}
            </div>
        )
    };
};

export default column;