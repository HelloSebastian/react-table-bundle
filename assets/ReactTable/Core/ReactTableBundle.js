import React, {Component} from 'react';
import ReactTable from "react-table-6";
import {json} from "../../util/util";
import buildTextColumn from './Columns/TextColumn';
import buildActionColumn from './Columns/ActionColumn';

export default class ReactTableBundle extends Component {

    constructor(props) {
        super(props);

        this.state = {
            data: [],
            loading: true,
            pages: -1,
            init: false
        };

        this.onFetchData = this.onFetchData.bind(this);
        this.buildColumns = this.buildColumns.bind(this);

        console.log("Default Table", this.props);
    }

    componentDidMount() {
        this.setState({
            columns: this.buildColumns(this.props.columns),
            init: true
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

    onFetchData(state) {

        // show the loading overlay
        this.setState({loading: true});

        // fetch your data
        json(this.props.callbackUrl, {
            method: "POST",
            body: JSON.stringify({
                page: state.page,
                pageSize: state.pageSize,
                sorted: state.sorted,
                filtered: state.filtered,
                isCallback: true
            })
        }).then(data => {
            console.log("data", data);

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
                onFetchData={this.onFetchData}
            />
        );
    }
}