<?php
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$nombreSidebar = trim(($cliente_info['nombre'] ?? '') . ' ' . ($cliente_info['apellido'] ?? ''));
if ($nombreSidebar === '') $nombreSidebar = 'Cliente';

$iniciales = strtoupper(
  substr($cliente_info['nombre'] ?? 'C', 0, 1) .
  substr($cliente_info['apellido'] ?? '', 0, 1)
);
if ($iniciales === '') $iniciales = 'C';

$pagina_activa = $pagina_activa ?? '';

$cuotasVencidas = (int)($cuotas_vencidas ?? 0);
$notiCount      = (int)($noti_no_leidas ?? 0);

// Obtener estado de validación del cliente
$estado_validacion = $cliente_info['estado_validacion'] ?? 'pendiente';
$puede_chatear = ($estado_validacion === 'aprobado');
?>
<aside class="sidebar">

  <!-- BRAND -->
  <div class="sidebar__brand">
    <h1>Préstamo Líder</h1>
    <p>Portal de Clientes</p>
  </div>

  <!-- USER -->
  <div class="sidebar__user">
    <div class="avatar"><?php echo h($iniciales); ?></div>
    <div class="user__meta">
      <div class="name">
        <?php echo h($nombreSidebar); ?>
        <?php if ($estado_validacion === 'aprobado'): ?>
          <span style="color: #10b981; font-size: 1rem; margin-left: 4px;" title="Documentos aprobados">✓</span>
        <?php elseif ($estado_validacion === 'rechazado'): ?>
          <span style="color: #ef4444; font-size: 1rem; margin-left: 4px;" title="Documentos rechazados">✗</span>
        <?php endif; ?>
      </div>
      <div class="mail"><?php echo h($cliente_info['email'] ?? ''); ?></div>
    </div>
  </div>

  <!-- NAV -->
  <nav class="sidebar__nav">

    <!-- DASHBOARD -->
    <a class="navlink <?php echo $pagina_activa==='dashboard'?'active':''; ?>" href="dashboard_clientes.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          d="M3 12l2-2 7-7 7 7M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0v-4a1 1 0 011-1h2a1 1 0 011 1v4m-6 0h6"/>
      </svg>
      Dashboard
    </a>

    <!-- PAGOS -->
    <a class="navlink <?php echo $pagina_activa==='pagos'?'active':''; ?>" href="pagos.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
      </svg>
      Pagos
      <?php if ($cuotasVencidas > 0): ?>
        <span class="pill"><?php echo $cuotasVencidas; ?></span>
      <?php endif; ?>
    </a>

    <!-- PERFIL -->
    <a class="navlink <?php echo $pagina_activa==='perfil'?'active':''; ?>" href="perfil_clientes.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      Mi Perfil
    </a>

    <!-- NOTIFICACIONES -->
    <a class="navlink <?php echo $pagina_activa==='notificaciones'?'active':''; ?>" href="notificaciones_clientes.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1"/>
      </svg>
      Notificaciones
      <?php if ($notiCount > 0): ?>
        <span class="pill blue"><?php echo $notiCount; ?></span>
      <?php endif; ?>
    </a>

    <!-- HABLAR CON ASESOR -->
    <?php if ($puede_chatear): ?>
      <!-- Cliente APROBADO - Enlace activo -->
      <a class="navlink <?php echo $pagina_activa==='chat'?'active':''; ?>" href="hablar_con_asesor.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        Hablar con Asesor
        <span class="pill" style="background:#10b981; margin-left:auto; font-size:0.7rem;">✓</span>
      </a>
    <?php elseif ($estado_validacion === 'rechazado'): ?>
      <!-- Cliente RECHAZADO - Bloqueado con mensaje -->
      <div class="navlink" style="opacity:.5; cursor:not-allowed; position:relative;" 
           title="Tus documentos fueron rechazados. Por favor, vuelve a cargarlos para poder acceder al chat.">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        Hablar con Asesor
        <span class="pill" style="background:#ef4444; margin-left:auto; font-size:0.7rem;">✗</span>
      </div>
    <?php else: ?>
      <!-- Cliente PENDIENTE/EN_REVISION - Deshabilitado temporalmente -->
      <div class="navlink" style="opacity:.55; cursor:not-allowed;" 
           title="Debes completar y validar tu documentación para acceder al chat">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        Hablar con Asesor
        <span class="pill blue" style="margin-left:auto; font-size:0.7rem;">⏳</span>
      </div>
    <?php endif; ?>

  </nav>

  <!-- FOOTER -->
  <div class="sidebar__footer">
    <a class="logout" href="logout.php">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
      </svg>
      Cerrar Sesión
    </a>
  </div>

</aside>

<style>
/* Estilos adicionales para los badges de estado */
.pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.15rem 0.5rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 700;
  background: #ef4444;
  color: white;
  min-width: 20px;
  height: 18px;
}

.pill.blue {
  background: #3b82f6;
}

/* Tooltip mejorado para enlaces bloqueados */
[title] {
  position: relative;
}

.navlink[title]:hover::after {
  content: attr(title);
  position: absolute;
  left: 100%;
  top: 50%;
  transform: translateY(-50%);
  margin-left: 1rem;
  padding: 0.5rem 0.75rem;
  background: rgba(0, 0, 0, 0.9);
  color: white;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  white-space: nowrap;
  max-width: 200px;
  white-space: normal;
  z-index: 1000;
  pointer-events: none;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

@media (max-width: 768px) {
  .navlink[title]:hover::after {
    display: none; /* Ocultar tooltip en móviles */
  }
}
</style>