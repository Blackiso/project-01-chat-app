export default class WS{
    constructor( url, array ){
        this.url = url;
        this.array = array;
        this.ws = new WebSocket( this.url, this.array );
    }
    send( data ){
        this.ws.send( data );
    }
    startListening( returnData ){
        this.listen = this.ws.addEventListener('message', e=>{
            returnData(e);
        });
    }
    stopListening(){
        this.ws.removeEventListener('message', this.listen);
    }
}