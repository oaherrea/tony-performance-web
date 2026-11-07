    </main>

    <footer class="bg-dark text-white text-center py-3 mt-4">
        <div class="container">
            <p class="mb-0">© <?php echo date('Y'); ?> Tony Performance Center. Todos los derechos reservados.</p>
            <small>Proyecto Final Integrador de Programación V con PHP, MySQL y Bootstrap · © by Ós</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo isset($ruta) ? $ruta : ''; ?>js/eventos.js"></script>
</body>

</html>
