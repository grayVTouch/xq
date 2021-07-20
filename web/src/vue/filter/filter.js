Vue.filter('zeroFill' , (value) => {
    if (!G.isNumeric(value)) {
        return value;
    }
    return value < 10 ? '0' + value : value;
});


Vue.filter('getKeyMappingValue' , (value , mappings) => {
    for (let k in mappings)
    {
        if (k == value) {
            return mappings[k];
        }
    }
    return '';
});
