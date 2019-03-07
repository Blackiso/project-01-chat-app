import Variables from "./variablesCall";
import store from "../store/store";
import ajax from "./ajax";

function getMessages(){
    //console.log('getmessages -----------------------------------------------')
    let { host, paths } = Variables,
        { messages_path } = paths,
        { messages, room_info } = store,
        { room_ID } = room_info,
        id = 1;
    
    if( messages.length ) id = messages.slice().reverse()[0]['id'];

    let request = new ajax(`${host}${messages_path}${room_ID}?filter=new&id=${id}`, 'GET', {
            body: {},
            handle: handleResponse
        });
    return request;
}
function handleResponse(type, e ){
    
    let { getMessagesRequest:l } = store; 
    l.forEach(e=> e.abort() );

    if( type === 'response' ){
        //console.log('retreive data');
        console.log(e);
        let { messages } = store;
        store.messages = [...messages, ...e];
    }else{
        //console.log("retry to get messages...");
    }
    store.getMessagesRequest = [ getMessages() ];
}

export {
    getMessages
};