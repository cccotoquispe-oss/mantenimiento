<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
require_once __DIR__ . '/config.php';

function respond($status, $data) {
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
  exit;
}
function db() {
  static $pdo = null;
  if ($pdo) return $pdo;
  $dsn = 'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4';
  $pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASSWORD, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
  ]);
  $pdo->exec("CREATE TABLE IF NOT EXISTS planificador_data (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    orders_json LONGTEXT NOT NULL,
    operators_json LONGTEXT NOT NULL,
    avisos_json LONGTEXT NOT NULL,
    activities_json LONGTEXT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  // Migración automática: agrega la columna al sitio ya instalado sin borrar datos previos.
  $check = $pdo->query("SHOW COLUMNS FROM planificador_data LIKE 'activities_json'")->fetch();
  if (!$check) $pdo->exec("ALTER TABLE planificador_data ADD COLUMN activities_json LONGTEXT NULL AFTER avisos_json");
  return $pdo;
}
try {
  $pdo = db();
  $action = $_GET['action'] ?? '';
  if ($action === 'load' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $row = $pdo->query('SELECT orders_json, operators_json, avisos_json, activities_json, updated_at FROM planificador_data WHERE id=1')->fetch();
    if (!$row) respond(200, ['ok'=>true, 'has_data'=>false]);
    $orders = json_decode($row['orders_json'], true);
    $operators = json_decode($row['operators_json'], true);
    $avisos = json_decode($row['avisos_json'], true);
    $activities = $row['activities_json'] ? json_decode($row['activities_json'], true) : [];
    if (!is_array($orders) || !is_array($operators) || !is_array($avisos) || !is_array($activities)) respond(500, ['ok'=>false,'error'=>'Los datos almacenados no tienen un formato válido.']);
    respond(200, ['ok'=>true, 'has_data'=>true, 'updated_at'=>$row['updated_at'], 'data'=>['orders'=>$orders,'operators'=>$operators,'avisos'=>$avisos,'activities'=>$activities]]);
  }
  if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $provided = $_SERVER['HTTP_X_ADMIN_KEY'] ?? '';
    if (ADMIN_KEY === 'CAMBIA_ESTA_CLAVE_POR_UNA_LARGA_Y_PRIVADA' || !hash_equals(ADMIN_KEY, $provided)) {
      respond(401, ['ok'=>false,'error'=>'Clave de actualización no configurada o incorrecta.']);
    }
    $raw = file_get_contents('php://input');
    if (strlen($raw) > 12 * 1024 * 1024) respond(413, ['ok'=>false,'error'=>'El archivo de datos supera el límite permitido.']);
    $payload = json_decode($raw, true);
    if (!is_array($payload) || !isset($payload['orders'],$payload['operators'],$payload['avisos']) || !is_array($payload['orders']) || !is_array($payload['operators']) || !is_array($payload['avisos'])) {
      respond(400, ['ok'=>false,'error'=>'El contenido recibido está incompleto o no es válido.']);
    }
    $orders = json_encode($payload['orders'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    $operators = json_encode($payload['operators'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    $avisos = json_encode($payload['avisos'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    // Si un HTML anterior (sin módulo Actividades) guarda información, conserva los registros existentes.
    if (array_key_exists('activities', $payload)) {
      if (!is_array($payload['activities'])) respond(400, ['ok'=>false,'error'=>'El listado de actividades no tiene un formato válido.']);
      $activitiesData = $payload['activities'];
    } else {
      $existing = $pdo->query('SELECT activities_json FROM planificador_data WHERE id=1')->fetch();
      $activitiesData = ($existing && $existing['activities_json']) ? json_decode($existing['activities_json'], true) : [];
      if (!is_array($activitiesData)) $activitiesData = [];
    }
    $activities = json_encode($activitiesData, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    $stmt = $pdo->prepare('INSERT INTO planificador_data (id,orders_json,operators_json,avisos_json,activities_json) VALUES (1,?,?,?,?) ON DUPLICATE KEY UPDATE orders_json=VALUES(orders_json), operators_json=VALUES(operators_json), avisos_json=VALUES(avisos_json), activities_json=VALUES(activities_json)');
    $stmt->execute([$orders,$operators,$avisos,$activities]);
    respond(200, ['ok'=>true,'message'=>'Datos guardados correctamente.','updated_at'=>date('Y-m-d H:i:s')]);
  }
  respond(400, ['ok'=>false,'error'=>'Acción no reconocida.']);
} catch (Throwable $e) {
  // El detalle técnico queda en el log del servidor; no se exponen credenciales al navegador.
  error_log('Planificador API: ' . $e->getMessage());
  respond(500, ['ok'=>false,'error'=>'Error del servidor o de conexión MySQL. Revisa config.php y la configuración de MySQL.']);
}
