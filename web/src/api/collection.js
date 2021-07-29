
export default {
    destroy (id , query , data) {
        return Http.delete( `${TopContext.api}/collection/${id}` , query , data);
    },
};
