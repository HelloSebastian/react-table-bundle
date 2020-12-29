import React, {Component} from 'react';
import ReactTable from "react-table-6";
import {json} from "../../util/util";
import buildTextColumn from './Columns/TextColumn';
import buildActionColumn from './Columns/ActionColumn';
import Cookies from 'universal-cookie';


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

        this.getPersistenceFromCookies = this.getPersistenceFromCookies.bind(this);

        console.log("Default Table", this.props);
    }

    getPersistenceFromCookies() {
        const {persistenceOptions, tableName} = this.props;

        const cookies = new Cookies();

        let persistedState = {
            sorted: [],
            resized: [],
            filtered: [],
            page: undefined,
            page_size: undefined
        };

        if (cookies.get("table_" + tableName)) {
            const cookiesPersistenceState = cookies.get("table_" + tableName);

            Object.keys(persistenceOptions).forEach(option => {
                if (persistenceOptions[option]) {
                    persistedState = {
                        ...persistedState,
                        [option]: cookiesPersistenceState[option]
                    };
                } else {
                    //reset cookie
                    cookiesPersistenceState[option] = persistedState[option];
                }
            });

            //update cookies
            cookies.set("table_" + tableName, JSON.stringify(cookiesPersistenceState), {path: '/'});
        } else {
            //if key not found, init cookies
            cookies.set("table_" + tableName, JSON.stringify(persistedState), {path: '/'});
        }

        return persistedState;
    }

    componentDidMount() {
        this.setState({
            columns: this.buildColumns(this.props.columns),
            init: true,
            ...this.getPersistenceFromCookies()
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
                const persistedState = cookies.get("table_" + tableName);
                persistedState[key] = state;
                cookies.set("table_" + tableName, JSON.stringify(persistedState), {path: '/'});

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