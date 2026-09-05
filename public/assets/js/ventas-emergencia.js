/**
 * Módulo de Venta de Emergencia — Solo Boticas
 */
const $ve = (id) => document.getElementById(id);

let veCarrito = []; // { cod_producto, nombre_producto, precio_piso, precio_venta, stock, cantidad }
let veBuscarTimer = null;

function veFmt(n) {
    return 'S/ ' + (isNaN(n) ? '0.00' : n.toFixed(2));
}

function veMostrarAlert(txt, tipo = 'error') {
    const el = $ve('veAlert');
    el.textContent = txt;
    el.className = `caja-alert caja-alert--${tipo}`;
    el.hidden = false;
}
function veOcultarAlert() {
    $ve('veAlert').hidden = true;
}

function veEsc(s) {
    const d = document.createElement('div');
    d.textContent = s ?? '';
    return d.innerHTML;
}

// ── Selector de local (grande, para evitar equivocarse de tienda) ──────
function veSetLocal(id) {
    id = String(id);
    const actual = $ve('veLocal').value;

    if (veCarrito.length > 0 && id !== actual) {
        veMostrarAlert('Ya tienes productos en el ticket de otro local. Vacía el ticket (Limpiar) antes de cambiar de local.');
        return;
    }

    $ve('veLocal').value = id;
    document.querySelectorAll('.ve-local-btn').forEach(b => {
        b.classList.toggle('is-active', b.dataset.local === id);
    });
    try { localStorage.setItem('ve_local_actual', id); } catch (e) {}

    $ve('veBuscar').value = '';
    $ve('veResultados').hidden = true;
}

document.querySelectorAll('.ve-local-btn').forEach(btn => {
    btn.addEventListener('click', () => veSetLocal(btn.dataset.local));
});

(function veInitLocal() {
    let saved = '2';
    try { saved = localStorage.getItem('ve_local_actual') || '2'; } catch (e) {}
    veSetLocal(saved);
})();

// ── Búsqueda de productos ──────────────────────────────
$ve('veBuscar').addEventListener('input', (e) => {
    clearTimeout(veBuscarTimer);
    const q = e.target.value.trim();

    if (q.length < 2) {
        $ve('veResultados').hidden = true;
        return;
    }

    veBuscarTimer = setTimeout(() => veBuscarProductos(q), 250);
});

async function veBuscarProductos(q) {
    const idLocal = $ve('veLocal').value;
    const cont = $ve('veResultados');

    try {
        const r   = await fetch(`${BASE}/ventas-emergencia/api/productos?id_local=${idLocal}&q=${encodeURIComponent(q)}`);
        const res = await r.json();
        const productos = (res.data && res.data.productos) || [];

        cont.innerHTML = '';

        if (productos.length === 0) {
            cont.innerHTML = '<div class="ve-resultado-vacio">Sin resultados</div>';
        } else {
            const clsActivo = (loc) => String(loc) === String(idLocal) ? ' ve-col-local--activo' : '';
            cont.innerHTML = `
                <div class="ve-resultado-header">
                    <span>Código</span><span>Nombre</span><span style="text-align:right;">Precio</span>
                    <span class="ve-col-local${clsActivo(2)}">L2</span>
                    <span class="ve-col-local${clsActivo(3)}">L3</span>
                    <span class="ve-col-local${clsActivo(4)}">L4</span>
                </div>
            `;
            productos.forEach(p => {
                const stockActual = parseFloat(p.stock_actual) || 0;
                const item = document.createElement('div');
                item.className = 've-resultado-item' + (stockActual <= 0 ? ' ve-resultado-item--sin-stock' : '');
                const stockCelda = (v) => (v === null || v === undefined) ? '—' : parseFloat(v);
                item.innerHTML = `
                    <span class="ve-resultado-item__cod">${veEsc(p.cod_producto)}</span>
                    <span class="ve-resultado-item__nombre">${veEsc(p.nombre_producto)}</span>
                    <span class="ve-resultado-item__precio">${veFmt(parseFloat(p.precio_venta))}</span>
                    <span class="ve-col-local${clsActivo(2)}">${stockCelda(p.stock_2)}</span>
                    <span class="ve-col-local${clsActivo(3)}">${stockCelda(p.stock_3)}</span>
                    <span class="ve-col-local${clsActivo(4)}">${stockCelda(p.stock_4)}</span>
                `;
                item.addEventListener('click', () => veAgregarAlCarrito(p, stockActual));
                cont.appendChild(item);
            });
        }
        cont.hidden = false;
    } catch (err) {
        veMostrarAlert('Error al buscar productos: ' + err.message);
    }
}

// Cierra el desplegable al hacer click fuera
document.addEventListener('click', (e) => {
    if (!e.target.closest('.ve-buscador')) {
        $ve('veResultados').hidden = true;
    }
});

// ── Carrito ─────────────────────────────────────────────
function veAgregarAlCarrito(p, stockActual) {
    const existente = veCarrito.find(i => i.cod_producto === p.cod_producto);
    if (existente) {
        existente.cantidad += 1;
    } else {
        veCarrito.push({
            cod_producto: p.cod_producto,
            nombre_producto: p.nombre_producto,
            precio_piso: parseFloat(p.precio_piso) || 0,
            precio_venta: parseFloat(p.precio_venta),
            stock: stockActual ?? 0,
            cantidad: 1,
        });
    }
    $ve('veBuscar').value = '';
    $ve('veResultados').hidden = true;
    veRenderCarrito();
}

// Semáforo del precio según % sobre el costo — nunca se muestra el costo en sí.
// Rojo: está en el piso (precio == costo). Amarillo: margen menor a 10%. Normal: el resto.
function veClasePrecio(item) {
    if (!item.precio_piso || item.precio_piso <= 0) return '';
    const margen = (item.precio_venta - item.precio_piso) / item.precio_piso;
    if (margen <= 0) return 've-input--precio-rojo';
    if (margen < 0.10) return 've-input--precio-naranja';
    return 've-input--precio-azul';
}

// Rojo si la cantidad supera el stock de referencia — solo aviso, NUNCA bloquea
// (el stock puede estar desactualizado y igual se puede vender/descargar).
function veClaseCantidad(item) {
    return item.cantidad > item.stock ? 've-input--cantidad-rojo' : '';
}

function veRenderCarrito() {
    const cont = $ve('veCarrito');
    cont.innerHTML = '';

    if (veCarrito.length === 0) {
        cont.innerHTML = '<tr id="veCartEmptyRow"><td colspan="7" class="ve-cart-empty">Aún no agregas productos.</td></tr>';
        veRenderTotales();
        return;
    }

    veCarrito.forEach((item, idx) => {
        const subtotal = item.precio_venta * item.cantidad;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="ve-cod">${veEsc(item.cod_producto)}</td>
            <td class="ve-nombre">${veEsc(item.nombre_producto)}</td>
            <td class="ve-col-num">
                <input type="number" class="ve-input ${veClasePrecio(item)}" step="0.01"
                       value="${item.precio_venta}" data-idx="${idx}" data-role="precio">
            </td>
            <td class="ve-col-num">
                <input type="number" class="ve-input ${veClaseCantidad(item)}" min="1" step="1"
                       value="${item.cantidad}" data-idx="${idx}" data-role="cantidad">
            </td>
            <td class="ve-col-num ve-subtotal">${veFmt(subtotal)}</td>
            <td class="ve-col-num ve-stock">${item.stock}</td>
            <td><button type="button" class="ve-remove" data-idx="${idx}" title="Quitar">✕</button></td>
        `;
        cont.appendChild(row);
    });

    cont.querySelectorAll('input[data-role="cantidad"]').forEach(inp => {
        inp.addEventListener('input', (e) => {
            const idx  = parseInt(e.target.dataset.idx, 10);
            const item = veCarrito[idx];
            item.cantidad = parseInt(e.target.value, 10) || 0;
            e.target.className = 've-input ' + veClaseCantidad(item);
            veActualizarSubtotalFila(idx);
        });
    });
    cont.querySelectorAll('input[data-role="precio"]').forEach(inp => {
        // Solo color de referencia (rojo/naranja/azul) — no se fuerza ni se corrige
        // lo que la vendedora escribe; el límite real se valida al guardar (servidor).
        inp.addEventListener('input', (e) => {
            const idx  = parseInt(e.target.dataset.idx, 10);
            const item = veCarrito[idx];
            const val  = parseFloat(e.target.value);

            item.precio_venta = isNaN(val) ? 0 : val;
            e.target.className = 've-input ' + veClasePrecio(item);
            veActualizarSubtotalFila(idx);
        });
    });
    cont.querySelectorAll('.ve-remove').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const idx = parseInt(e.target.dataset.idx, 10);
            veCarrito.splice(idx, 1);
            veRenderCarrito();
        });
    });

    veRenderTotales();
}

function veActualizarSubtotalFila(idx) {
    const item = veCarrito[idx];
    const row  = $ve('veCarrito').children[idx];
    if (row) {
        row.querySelector('.ve-subtotal').textContent = veFmt(item.precio_venta * item.cantidad);
    }
    veRenderTotales();
}

function veRenderTotales() {
    const total = veCarrito.reduce((acc, i) => acc + (i.precio_venta * i.cantidad), 0);
    $ve('veTotal').textContent = veFmt(total);
}

$ve('veLimpiar').addEventListener('click', () => {
    veCarrito = [];
    veRenderCarrito();
    veOcultarAlert();
});

$ve('veGuardar').addEventListener('click', async () => {
    veOcultarAlert();

    if (veCarrito.length === 0) {
        veMostrarAlert('Agrega al menos un producto al ticket.');
        return;
    }
    if (veCarrito.some(i => i.cantidad <= 0)) {
        veMostrarAlert('Hay productos con cantidad inválida.');
        return;
    }

    const btn = $ve('veGuardar');
    btn.disabled = true;

    try {
        const r = await fetch(`${BASE}/ventas-emergencia/api/registrar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_local: parseInt($ve('veLocal').value, 10),
                items: veCarrito.map(i => ({
                    cod_producto: i.cod_producto,
                    nombre_producto: i.nombre_producto,
                    precio_venta: i.precio_venta,
                    cantidad: i.cantidad,
                })),
            }),
        });
        const res = await r.json();

        if (!res.success) {
            veMostrarAlert(res.message || 'Error al guardar la venta.');
            return;
        }

        veCarrito = [];
        veRenderCarrito();

        const el = $ve('veAlert');
        el.className = 'caja-alert caja-alert--ok';
        el.innerHTML = `Venta guardada. Total: ${veFmt(res.data.total)} — `
            + `<a href="${BASE}/ventas-emergencia/${res.data.id}/imprimir" target="_blank" style="font-weight:700;">🖨️ Imprimir nota</a>`;
        el.hidden = false;
    } catch (err) {
        veMostrarAlert('Error de red: ' + err.message);
    } finally {
        btn.disabled = false;
    }
});

veRenderCarrito();
