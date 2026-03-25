<!-- app/Views/diario/busca.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Busca - Diário Oficial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        mark { background: #fff176; padding: 0 2px; border-radius: 3px; }
        .resultado-card { border-left: 4px solid #0d6efd; }
        #spinner { display: none; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h4 class="mb-4">Busca no Diário Oficial</h4>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group">
                <input type="text" id="termo" class="form-control form-control-lg"
                       placeholder="Digite um nome ou palavra-chave..." minlength="3">
                <button class="btn btn-primary px-4" onclick="buscar()">Buscar</button>
            </div>
            <div id="spinner" class="mt-3 text-center text-muted">
                <div class="spinner-border spinner-border-sm me-2"></div>
                Buscando nos diários indexados...
            </div>
        </div>
    </div>

    <div id="resumo" class="mb-3 text-muted small"></div>
    <div id="resultados"></div>
</div>

<script>
async function buscar() {
    const termo = document.getElementById('termo').value.trim();
    if (termo.length < 3) { alert('Digite ao menos 3 caracteres.'); return; }

    document.getElementById('spinner').style.display = 'block';
    document.getElementById('resultados').innerHTML = '';
    document.getElementById('resumo').textContent = '';

    try {
        const resp = await fetch(`<?= base_url('diario/buscar') ?>?termo=${encodeURIComponent(termo)}`);
        const data = await resp.json();

        document.getElementById('spinner').style.display = 'none';

        if (!data.success) {
            document.getElementById('resumo').textContent = data.message;
            return;
        }

        document.getElementById('resumo').textContent =
            data.total > 0 ? `${data.total} resultado(s) encontrado(s) para "${termo}"` : `Nenhum resultado para "${termo}"`;

        const html = data.resultados.map(r => `
            <div class="card resultado-card mb-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="fw-bold">Edição ${r.edicao}</span>
                            <span class="badge bg-secondary ms-2">${r.tipo}</span>
                        </div>
                        <div class="text-muted small">${r.data} &bull; Pág. ${r.pagina}</div>
                    </div>
                    <p class="mb-2 small text-muted">${r.trecho}</p>
                    <a href="${r.pdf_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                        Abrir PDF
                    </a>
                </div>
            </div>
        `).join('');

        document.getElementById('resultados').innerHTML = html || '<p class="text-muted">Nenhum resultado.</p>';

    } catch (e) {
        document.getElementById('spinner').style.display = 'none';
        document.getElementById('resumo').textContent = 'Erro ao buscar. Tente novamente.';
    }
}

document.getElementById('termo').addEventListener('keydown', e => {
    if (e.key === 'Enter') buscar();
});
</script>
</body>
</html>