function checkArray(array, vars){
    vars.forEach(e=>{
        if( Boolean( array[e] ) ) array = array[e];
        else array = [];
    });
    return array;
}

export default checkArray;