import React, {Component} from 'react';
import ReactTable from "react-table-6";
import {json} from "../../util/util";
import buildTextColumn from './Columns/TextColumn';
import buildActionColumn from './Columns/ActionColumn';
import Cookies from 'universal-cookie';

const KEY = (key, tableName) => {
    return 'table_' + tableName + '_' + key;
};

export default class ReactTableBundle extends Component {

    constructor(props) {
        super(props);

        this.state = {
            data: [],
            loading: true,
            pages: -1,
            init: false,
            sorted: [],
            resized: [],
            filtered: [],
            page: undefined,
            page_size: undefined
        };

        this.onFetchData = this.onFetchData.bind(this);
        this.buildColumns = this.buildColumns.bind(this);
        this.saveStateToCookie = this.saveStateToCookie.bind(this);

        console.log("Default Table", this.props);
    }

    handleCookies(key, tableName, persistenceOptions) {
        const cookies = new Cookies();
        let value = [];
        if (key === "page" || key === "page_size") {
            value = undefined;
        }

        const COOKIE_KEY = KEY(key, tableName);

        if (persistenceOptions[key]) {
            if (cookies.get(COOKIE_KEY)) {
                return cookies.get(COOKIE_KEY);
            }
        } else {
            cookies.remove(COOKIE_KEY);
        }

        return value;
    }

    componentDidMount() {
        const {tableName, persistenceOptions} = this.props;

        let sorted = this.handleCookies("sorted", tableName, persistenceOptions);
        let resized = this.handleCookies("resized", tableName, persistenceOptions);
        let filtered = this.handleCookies("filtered", tableName, persistenceOptions);
        let page = this.handleCookies("page", tableName, persistenceOptions);
        let pageSize = this.handleCookies("page_size", tableName, persistenceOptions);

        this.setState({
            columns: this.buildColumns(this.props.columns),
            init: true,
            sorted: sorted,
            resized: resized,
            filtered: filtered,
            page: page,
            pageSize: pageSize
        });
    }

    buildColumns(columns) {
        return columns.map(col => {
            switch (col.type) {
                case "text":
                    return buildTextColumn(col);
                case "action":
                    return buildActionColumn(col);
                default:
                    return buildTextColumn(col);
            }
        });
    }

    saveStateToCookie(state, key) {
        const {persistenceOptions, tableName} = this.props;

        if (persistenceOptions[key]) {
            clearTimeout(this.timeoutSaveCookies);
            this.timeoutSaveCookies = setTimeout(() => {
                const cookies = new Cookies();
                const COOKIE_KEY = KEY(key, tableName);
                cookies.set(COOKIE_KEY, JSON.stringify(state), {path: '/'});
            }, 800);
        }

        this.setState({
            [key]: state
        });
    }

    onFetchData(state) {

        // show the loading overlay
        this.setState({loading: true});

        // fetch your data
        json(this.props.url, {
            method: "POST",
            body: JSON.stringify({
                page: state.page,
                pageSize: state.pageSize,
                sorted: state.sorted,
                filtered: state.filtered,
                isCallback: true
            })
        }).then(data => {
            this.setState({
                data: data.data,
                loading: false,
                pages: Math.ceil(data.totalCount / state.pageSize),
            });
        });
    }

    render() {

        if (!this.state.init) {
            return <div>Loading ...</div>;
        }

        return (
            <ReactTable
                {...this.props.tableProps}
                data={this.state.data}
                columns={this.state.columns}
                pages={this.state.pages}
                loading={this.state.loading}
                manual
                sorted={this.state.sorted}
                resized={this.state.resized}
                filtered={this.state.filtered}
                page={this.state.page}
                pageSize={this.state.page_size}
                onFetchData={this.onFetchData}
                onResizedChange={(state) => this.saveStateToCookie(state, "resized")}
                onSortedChange={(state) => this.saveStateToCookie(state, "sorted")}
                onFilteredChange={(state) => this.saveStateToCookie(state, "filtered")}
                onPageChange={(state) => this.saveStateToCookie(state, "page")}
                onPageSizeChange={(state) => this.saveStateToCookie(state, "page_size")}
            />
        );
    }
}