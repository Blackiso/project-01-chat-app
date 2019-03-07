import React from 'react';
import { observer } from 'mobx-react';
import history from '../functions/history';

class MainApp extends React.Component{
    render(){
        return(
            <div>
                <button onClick={this.create}>CREATE ROOM</button>
                <button onClick={this.join}>JOIN ROOM</button>
            </div>
        )
    }
    create(){
        history.push('/create_room');
    }
    join(){
        history.push('/join_room');
    }
}

export default observer( MainApp );