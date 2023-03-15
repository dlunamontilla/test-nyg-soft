function getCities() {

    const city = document.querySelector('#city');
    const departaments = document.querySelector("#departament");


    if (!(city instanceof HTMLSelectElement)) {
        return;
    }

    if (!(departaments instanceof HTMLSelectElement)) {
        return;
    }

    departaments.onchange = async function() {
        const id = Number(this.value)
        const response = await fetch(`cities?id=${id}`)

        const data = await response.json();

        city.textContent = "";

        data.forEach(register => {
            const { cities_id: ID, cities_city_name: name } =  register;

            let options = `
                <option value="${ID}">${name}</option>
            `;

            city.innerHTML += options;
        });
    }
    
}

getCities();


/**
 * Enviar datos al servidor.
 *
 * @param { HTMLFormElement } form - Formulario a ser procesado.
 * @returns
 */
async function sendToServer(form) {
    const formData = new FormData(form);
    const method = form.getAttribute("method") ?? "GET";

    const action = form.getAttribute('action') ?? '';

    const response = await fetch(action, {
        method,
        body: formData,
        credentials: 'same-origin'
    });

    if (!response.ok) {
        return {
            message: "No se pudo procesar exitosamente la petición"
        }
    }

    const data = await response.json();
    
    return data;
}

function saveUser() {
    const form = document.querySelector("#test-form");

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    form.onsubmit = async function(e) {
        e.preventDefault();

        await sendToServer(this);

        const response = await fetch('users');
        const data = await response.json();

        const tbody = document.querySelector("#tbody");

        if (!(tbody instanceof HTMLElement)) {
            return;
        }

        tbody.textContent = "";
        
        data.forEach(register => {
            const { DEP: departament, CIU: ciudad } = register;

            let registers = `
                <tr>
                    <td>${departament}</td>
                    <td>${ciudad}</td>
                </tr>
            `;

            tbody.innerHTML += registers;

            form.reset();
        });
    }
}

saveUser();

