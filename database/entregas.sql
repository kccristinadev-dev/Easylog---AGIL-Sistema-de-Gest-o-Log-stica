CREATE TABLE IF NOT EXISTS entregas (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    pedido_id VARCHAR(80) NULL,
    cliente VARCHAR(255) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    data_prevista DATE NULL,
    status ENUM('Pendente', 'Em separação', 'Em transporte', 'Entregue', 'Cancelada') NOT NULL DEFAULT 'Pendente',
    produtos_relacionados JSON NULL,
    quantidade INT NOT NULL DEFAULT 0,
    observacoes TEXT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_usuario_id (usuario_id),
    KEY idx_status (status),
    CONSTRAINT fk_entregas_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exemplo de consulta para listar entregas por usuário e sugerir rota:
-- SELECT id, cliente, endereco, status, data_prevista, latitude, longitude
-- FROM entregas
-- WHERE usuario_id = ? AND status NOT IN ('Entregue', 'Cancelada')
-- ORDER BY data_prevista ASC, endereco ASC;
