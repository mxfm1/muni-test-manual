<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Accesos al manual municipal y a su editor.">
    <title>Manual municipal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBaseUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>/home.css">
</head>
<body class="home">
    <main class="home-main">
        <section class="home-intro" aria-labelledby="home-title">
            <div class="home-eyebrow">
                <span class="home-eyebrow-mark" aria-hidden="true"></span>
                Plataforma de gestión municipal
            </div>
            <h1 id="home-title">Manual municipal</h1>
            <p>Elige el espacio que quieres visitar para consultar la documentación o administrar su contenido.</p>
        </section>

        <section class="home-destinations" aria-label="Destinos principales">
            <a class="destination-card destination-card-primary" href="/handbook">
                <span class="destination-icon" aria-hidden="true">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                    </svg>
                </span>
                <span class="destination-content">
                    <span class="destination-label">Consulta</span>
                    <span class="destination-title">Explorar el manual</span>
                    <span class="destination-description">Accedé a los módulos, tópicos y procedimientos publicados.</span>
                </span>
                <span class="destination-arrow" aria-hidden="true">→</span>
            </a>

            <a class="destination-card destination-card-secondary" href="/handbook/edit">
                <span class="destination-icon" aria-hidden="true">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                    </svg>
                </span>
                <span class="destination-content">
                    <span class="destination-label">Administración</span>
                    <span class="destination-title">Editar el manual</span>
                    <span class="destination-description">Creá y organizá módulos, tópicos y secciones del contenido.</span>
                </span>
                <span class="destination-arrow" aria-hidden="true">→</span>
            </a>
        </section>
    </main>
    <footer class="home-footer">Ilustre Municipalidad de Viña del Mar</footer>
</body>
</html>
