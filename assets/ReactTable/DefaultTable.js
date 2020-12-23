import React, {Component} from 'react';
import ReactTableBundle from "./Core/ReactTableBundle";


export default class DefaultTable extends Component {

    constructor(props) {
        super(props);
    }

    render() {
        return (
            <ReactTableBundle
                columns={this.props.table.columns}
                callbackUrl={this.props.table.url}
                tableProps={this.props.table.tableProps}
            />
        );
    }
}