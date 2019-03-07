import React from 'react';
import { observer } from 'mobx-react';
import { Scrollbars } from 'react-custom-scrollbars';
import ActiveUser from './ActiveUser';
import variablesCall from '../functions/variablesCall';
import store from '../store/store';
import renameUsers from '../functions/renameUsers';
import { toJS } from 'mobx';

class ActiveUsersList extends React.Component{
    render(){
        let { layout } = variablesCall,
            { activeUsers, roomInfo } = toJS(store),
            { active_users_title } = layout,
            ActiveUsers = renameUsers(activeUsers).map((e,i)=>{
                let { user_ID } = roomInfo;
                if( e['user_ID'] === user_ID ) return null;
                return <ActiveUser data={e} key={i} />
            });

        return(
            <div className="active-users">
                <div className="title">{ active_users_title } <i className="fas fa-plug"></i></div>
                <Scrollbars className="users">
                    { ActiveUsers }
                </Scrollbars>
            </div>
        )
    }
}

export default observer( ActiveUsersList );