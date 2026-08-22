<?php
/**
 * includes/db-session-handler.php
 *
 * Drop-in replacement for PHP's default file-based session storage.
 *
 * WHY THIS EXISTS:
 * On Vercel, every request can be served by a different, ephemeral
 * serverless function instance. PHP's default session handler stores
 * session data as a file under session.save_path (typically /tmp),
 * which is local to a single instance and NOT shared or guaranteed to
 * persist between requests. The result: a user logs in on one instance,
 * the next request lands on a different instance that has never seen
 * that session file, session_start() silently creates a brand-new empty
 * session, and the user appears to be randomly logged out.
 *
 * This handler stores session data in the MySQL database instead, which
 * is shared by every instance, so sessions survive no matter which
 * instance handles the request.
 *
 * USAGE:
 * Include and activate this BEFORE session_start() is called anywhere,
 * and before any HTML/output is sent. The best place is at the very top
 * of config/database.php (right after the PDO connection is created),
 * or in a small bootstrap file that every entry point requires first.
 *
 *     require_once __DIR__ . '/db-session-handler.php';
 *     register_db_session_handler($pdo); // pass your existing PDO connection
 *     session_start();
 *
 * Adjust the `$pdo` variable name/creation below to match however
 * config/database.php currently builds its PDO connection.
 */

class DbSessionHandler implements SessionHandlerInterface
{
    private PDO $pdo;
    private int $maxLifetime;

    public function __construct(PDO $pdo, int $maxLifetime = 1440)
    {
        $this->pdo = $pdo;
        $this->maxLifetime = $maxLifetime;
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT data FROM sessions WHERE id = :id AND last_activity > :expiry LIMIT 1'
        );
        $stmt->execute([
            ':id'     => $id,
            ':expiry' => time() - $this->maxLifetime,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['data'] : '';
    }

    public function write($id, $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sessions (id, data, last_activity)
             VALUES (:id, :data, :time)
             ON DUPLICATE KEY UPDATE data = :data2, last_activity = :time2'
        );

        return $stmt->execute([
            ':id'     => $id,
            ':data'   => $data,
            ':time'   => time(),
            ':data2'  => $data,
            ':time2'  => time(),
        ]);
    }

    public function destroy($id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function gc($max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE last_activity < :expiry');
        $stmt->execute([':expiry' => time() - $max_lifetime]);
        return $stmt->rowCount();
    }
}

/**
 * Registers the DB session handler and sets sensible cookie params.
 * Call this once, then call session_start().
 */
function register_db_session_handler(PDO $pdo): void
{
    // Keep sessions alive for 24 hours of inactivity — adjust as needed.
    $maxLifetime = 86400;
    ini_set('session.gc_maxlifetime', (string) $maxLifetime);

    $handler = new DbSessionHandler($pdo, $maxLifetime);
    session_set_save_handler($handler, true);

    // Make sure the cookie itself doesn't expire the session early,
    // and that it's sent correctly over HTTPS (Vercel is always HTTPS).
    session_set_cookie_params([
        'lifetime' => $maxLifetime,
        'path'     => '/',
        'secure'   => true,     // requires HTTPS, which Vercel provides
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}