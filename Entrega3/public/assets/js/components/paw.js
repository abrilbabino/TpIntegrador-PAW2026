class PAW {
  static nuevoElemento(tag, contenido = null, atributos = {}) {
    let elemento = document.createElement(tag);

    // Setear atributos dinámicamente
    for (const atributo in atributos) {
        elemento.setAttribute(atributo, atributos[atributo]);
    }

    if (contenido && contenido.tagName) {
        elemento.appendChild(contenido);
    } else {
        elemento.appendChild(document.createTextNode(contenido));
    }
    
    return elemento;
  }

    static cargarScript(nombre, url, fnCallback = () => {}) {
        let elemento = document.querySelector("#" + nombre);
        
        if (!elemento) {
            elemento = this.nuevoElemento("script", "", { src: url, id: nombre });
            
            if (fnCallback) {
                elemento.addEventListener("load", fnCallback);   
            }
            
            document.head.appendChild(elemento);
        }
        return elemento;
    }
}