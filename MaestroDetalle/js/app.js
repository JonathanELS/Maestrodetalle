// Funcion para confirmar borrar un registro
function borrarRegistro(id) {
    if (confirm("¿Borrar registro?") === true) {
        window.location = 'order.php?mod=D&id=' + id;
    }
}