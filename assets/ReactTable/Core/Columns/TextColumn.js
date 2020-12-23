import React from 'react';

const column = (config) => {

    const {filter: filterOption} = config;

    let Filter = null;
    switch (filterOption.type) {
        case "text":
            Filter = ({filter, onChange}) => (
                    <input
                        type="text"
                        placeholder={filterOption.options.placeholder}
                        onChange={(e) => (onChange(e.target.value))}
                        value={filter ? filter.value : ''}
                        style={{width: "100%"}}
                    />
                );
            break;
        case "select":
            console.log("filter", filterOption);

            Filter = ({filter, onChange}) => (
                <select
                    value={filter ? filter.value : ''}
                    onChange={(e) => onChange(e.target.value)}
                    style={{width: "100%"}}
                >
                    <option value="">{filterOption.options.placeholder}</option>
                    {Object.keys(filterOption.options.choices).map((value, key) => (
                        <option key={key} value={value}>{filterOption.options.choices[value]}</option>
                    ))}
                </select>
            );
            break;
    }

    return {
        ...config,
        Filter: Filter
    };
};

export default column;