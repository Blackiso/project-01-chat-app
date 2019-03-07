import React from 'react';
import { observer } from 'mobx-react';

class LeftSideBar extends React.Component{
    render(){
        return(
            <div className="left-bar">
                <div className="logo"><i className="fab fa-twitter"></i></div>
                <div className="buttons">
                    <button className="setting">
                        <i className="fas fa-share-square"></i>
                    </button>	
                    <button className="setting">
                        <i className="fas fa-cog"></i>
                    </button>	
                    <button className="log-out">
                        <i className="fas fa-sign-out-alt"></i>
                    </button>
                </div>	
            </div>
        )
    }
}

export default observer( LeftSideBar );