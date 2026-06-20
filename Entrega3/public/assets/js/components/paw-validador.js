class PAWValidador {
  // Emplea Array.from() y .filter() evaluando la propiedad nativa 'willValidate' para recolectar únicamente los campos de formulario sujetos a validación.
  // Apaga la validación visual nativa del navegador mediante el atributo 'novalidate'.
  constructor(formulario) {
    this.formulario = formulario;
    this.inputs = Array.from(
      this.formulario.querySelectorAll("input, textarea, select"),
    ).filter(
      (elemento) =>
        elemento.willValidate &&
        !elemento.disabled &&
        elemento.type !== "hidden",
    );

    this.formulario.setAttribute("novalidate", "true");
    this.registrarEventos();

    // Procesar posibles errores del servidor definidos mediante data-server-error
    this.inputs.forEach((input) => {
      if (input.dataset.serverError) {
        input.setCustomValidity(input.dataset.serverError);
        this.mostrarError(input, input.dataset.serverError);
      }
    });
  }

  // Asigna listeners.
  // Usa 'blur' para validar cuando el usuario termina de interactuar con el campo.
  // Usa 'input' condicionado para proveer feedback en tiempo real únicamente si el campo ya se encontraba en un estado inválido.
  registrarEventos() {
    this.inputs.forEach((input) => {
      input.addEventListener("blur", () => this.validarCampo(input));
      input.addEventListener("input", () => {
        if (input.classList.contains("input-invalido") && !input.dataset.serverError) {
          this.validarCampo(input);
        }
      });
      input.addEventListener("change", () => {
        if (input.dataset.serverError) {
          input.setCustomValidity(""); // Clear custom validity so HTML5 can validate again
          delete input.dataset.serverError;
        }
        if (input.type === "radio" || input.type === "file" || input.classList.contains("input-invalido")) {
          this.validarCampo(input);
        }
      });
    });

    this.formulario.addEventListener("submit", (event) =>
      this.handleSubmit(event),
    );
  }

  // Intercepta el evento de envío. 
  // Utiliza Array.prototype.reduce() para computar el estado global del formulario.
  // Si es inválido, detiene el request (preventDefault) y emplea Array.prototype.find() junto con el método nativo .focus() para dirigir la atención al primer campo erróneo.
  handleSubmit(event) {
    let formularioValido = true;
    let primerInvalido = null;

    this.obtenerCamposAValidar().forEach((input) => {
      const campoValido = this.validarCampo(input);
      if (!campoValido) {
        formularioValido = false;
        if (!primerInvalido) {
          primerInvalido = input;
        }
      }
    });

    if (!formularioValido) {
      event.preventDefault();
      if (primerInvalido) {
        primerInvalido.focus();
      }
    }
  }

  // validarCampo: Evalúa el estado mediante input.checkValidity() de la API de validación.
  validarCampo(input) {
    if (input.dataset.serverError) {
      input.setCustomValidity(input.dataset.serverError);
      this.mostrarError(input, input.dataset.serverError);
      return false;
    }

    input.setCustomValidity(this.obtenerErrorPersonalizado(input));

    if (input.checkValidity()) {
      this.limpiarError(input);
      return true;
    }

    const mensaje = this.obtenerMensajeError(input);
    this.mostrarError(input, mensaje);
    return false;
  }

  // mostrarError: Inyecta el mensaje dinámicamente usando insertAdjacentElement('afterend').
  mostrarError(input, mensaje) {
    this.limpiarError(input);
    input.classList.add("input-invalido");

    const mensajeElemento = PAW.nuevoElemento("span", mensaje, {
      class: "msg-error",
    });

    const contenedorMensaje = this.obtenerContenedorMensaje(input);
    if (contenedorMensaje) {
      contenedorMensaje.insertAdjacentElement("afterend", mensajeElemento);
    } else {
      input.insertAdjacentElement("afterend", mensajeElemento);
    }
  }

  // limpiarError: Realiza DOM Traversal usando input.nextElementSibling para identificar y remover (.remove()) el nodo de error asociado sin afectar el resto del árbol.
  limpiarError(input) {
    input.classList.remove("input-invalido");

    const contenedorCentral = this.obtenerContenedorMensaje(input) || input;

    const siguiente = contenedorCentral.nextElementSibling;
    if (siguiente && siguiente.classList.contains("msg-error")) {
      siguiente.remove();
    }
  }

  obtenerCamposAValidar() {
    const gruposRadio = new Set();

    return this.inputs.filter((input) => {
      if (input.type !== "radio") {
        return true;
      }

      if (gruposRadio.has(input.name)) {
        return false;
      }

      gruposRadio.add(input.name);
      return true;
    });
  }

  obtenerContenedorMensaje(input) {
    if (input.type === "radio") {
      return input.closest("fieldset") || input;
    }

    if (input.parentElement && input.parentElement.classList.contains("campo-contraseña")) {
      return input.parentElement;
    }

    return input;
  }

  obtenerErrorPersonalizado(input) {
    if (input.dataset.trimRequired === "true" && input.value.trim() === "") {
      return input.dataset.requiredMessage || "Este campo es obligatorio.";
    }

    if (input.type === "date" && input.dataset.noFuture === "true" && input.value) {
      const fechaIngresada = new Date(`${input.value}T00:00:00`);
      const hoy = new Date();
      hoy.setHours(0, 0, 0, 0);

      if (fechaIngresada > hoy) {
        return input.dataset.futureMessage || "La fecha no puede ser futura.";
      }
    }

    if (input.type === "date" && input.dataset.noPast === "true" && input.value) {
      const fechaIngresada = new Date(`${input.value}T00:00:00`);
      const hoy = new Date();
      hoy.setHours(0, 0, 0, 0);

      if (fechaIngresada < hoy) {
        return input.dataset.pastMessage || "La fecha no puede ser pasada.";
      }
    }

    if (input.type === "file" && input.files && input.files.length > 0) {
      const archivo = input.files[0];
      const maximoBytes = Number(input.dataset.maxFileSize || 0);

      if (maximoBytes > 0 && archivo.size > maximoBytes) {
        return input.dataset.maxFileMessage || "El archivo supera el tamaño máximo permitido.";
      }

      if (input.accept && !this.archivoCumpleAccept(archivo, input.accept)) {
        return input.dataset.fileTypesMessage || "El tipo de archivo no es válido.";
      }
    }

    return "";
  }

  archivoCumpleAccept(archivo, accept) {
    const reglas = accept.split(",").map((regla) => regla.trim().toLowerCase()).filter(Boolean);
    const nombre = archivo.name.toLowerCase();
    const tipo = archivo.type.toLowerCase();

    return reglas.some((regla) => {
      if (regla.startsWith(".")) {
        return nombre.endsWith(regla);
      }

      if (regla.endsWith("/*")) {
        return tipo.startsWith(regla.slice(0, -1));
      }

      return tipo === regla;
    });
  }

  // Accede al objeto ValidityState (input.validity).
  // Mapea las flags booleanas nativas (valueMissing, typeMismatch, tooShort) generadas automáticamente por los atributos hacia mensajes legibles en español.
  obtenerMensajeError(input) {
    const validity = input.validity;

    if (validity.customError) {
      return input.validationMessage;
    }

    if (validity.valueMissing) {
      if (input.type === "radio") {
        return "Debe seleccionar una opción.";
      }
      return "Este campo es obligatorio.";
    }

    if (validity.typeMismatch) {
      if (input.type === "email") {
        return "Ingrese un correo electrónico válido.";
      }
      if (input.type === "url") {
        return "Ingrese una URL válida.";
      }
      return "El formato ingresado no es válido.";
    }

    if (validity.tooShort) {
      return `Debe tener al menos ${input.minLength} caracteres.`;
    }

    if (validity.tooLong) {
      return `No puede superar ${input.maxLength} caracteres.`;
    }

    if (validity.rangeUnderflow) {
      return `El valor debe ser mayor o igual a ${input.min}.`;
    }

    if (validity.rangeOverflow) {
      return `El valor debe ser menor o igual a ${input.max}.`;
    }

    if (validity.patternMismatch) {
      if (input.name === "nombre_usuario") {
        return "Solo puede contener letras, números, guiones y puntos.";
      }
      if (input.name === "nombre" || input.name === "apellido") {
        return "Solo se permiten letras y espacios.";
      }
      if (input.name === "dni") {
        return "El DNI debe contener solo números (y puntos opcionales).";
      }
      if (input.name === "contacto" || input.name === "telefono") {
        return "Formato inválido. Solo números, espacios, +, y guiones.";
      }
      if (input.title) {
        return input.title;
      }
      return "El formato no coincide con el esperado.";
    }

    if (validity.stepMismatch) {
      return "El valor ingresado no cumple con el paso requerido.";
    }

    if (validity.badInput) {
      return "El valor ingresado no es válido.";
    }

    return "El campo no es válido.";
  }
}
