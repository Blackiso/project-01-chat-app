import React from 'react';
import { Scrollbars } from 'react-custom-scrollbars';
import { observer } from 'mobx-react';
import store from '../store/store';
import Message from './Message';
import { toJS } from 'mobx';

class MessagesContainer extends React.Component{
    constructor(){
        super();
        this.state = { over: false }
        this.toBottom = this.toBottom.bind( this );
        this.mouseOver = this.mouseOver.bind( this );
    }
    render(){
        let { messages } = toJS( store ),
        { over } = this.state,
        msgItem = messages.map((e,i)=>{
            return <Message data={e} key={i} />
        });
        return(
            <Scrollbars 
                className="messages-container"
                onMouseOver={ ()=> this.mouseOver( true ) }
                onMouseEnter={ ()=> this.mouseOver( true ) }
                onMouseMove={ ()=> this.mouseOver( true ) }
                onMouseUp={ ()=> this.mouseOver( true ) }
                onMouseDown={ ()=> this.mouseOver( true ) }
                onMouseLeave={ ()=> this.mouseOver( false ) }
                onUpdate={ this.toBottom }
                ref={ e=> this.scrollbar = e }
            >
                { msgItem }
            </Scrollbars>
        )
    }
    mouseOver(e){
        this.setState({ over: e });
    }
    toBottom(e){
        let { over } = this.state;
        if( !over ) this.scrollbar.scrollToBottom();
    }
}

export default observer( MessagesContainer );