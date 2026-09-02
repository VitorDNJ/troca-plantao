-- =====================================================================
-- SISTEMA DE SOLICITAÇÃO DE TROCA E PASSAGEM DE PLANTÃO
-- Schema MySQL / MariaDB
-- =====================================================================

CREATE DATABASE IF NOT EXISTS troca_plantao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE troca_plantao;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- PERFIS
-- ---------------------------------------------------------------------
CREATE TABLE perfis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,   -- ADMIN, COORDENADOR, COLABORADOR
    nome VARCHAR(60) NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- SETORES
-- ---------------------------------------------------------------------
CREATE TABLE setores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    status ENUM('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- USUARIOS
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL,
    setor_id INT NOT NULL,
    funcao VARCHAR(100) NOT NULL,
    perfil_id INT NOT NULL,
    status ENUM('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
    senha_hash VARCHAR(255) NOT NULL,
    trocar_senha_primeiro_acesso TINYINT(1) NOT NULL DEFAULT 1,
    tentativas_login INT NOT NULL DEFAULT 0,
    bloqueado_ate DATETIME NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_setor FOREIGN KEY (setor_id) REFERENCES setores(id),
    CONSTRAINT fk_usuarios_perfil FOREIGN KEY (perfil_id) REFERENCES perfis(id)
) ENGINE=InnoDB;

-- Coordenadores podem estar vinculados a mais de um setor
CREATE TABLE usuarios_setores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    setor_id INT NOT NULL,
    UNIQUE KEY uq_usuario_setor (usuario_id, setor_id),
    CONSTRAINT fk_us_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_us_setor FOREIGN KEY (setor_id) REFERENCES setores(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- PERIODOS DE CONTROLE
-- ---------------------------------------------------------------------
CREATE TABLE periodos_controle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_inicial DATE NOT NULL,
    data_final DATE NOT NULL,
    limite_trocas INT NOT NULL DEFAULT 2,
    limite_passagens INT NOT NULL DEFAULT 2,
    status ENUM('FUTURO','ATIVO','ENCERRADO','INATIVO') NOT NULL DEFAULT 'FUTURO',
    regra_troca_entre_periodos ENUM('PERMITIDA','PROIBIDA','SOMENTE_AUTORIZACAO') NOT NULL DEFAULT 'SOMENTE_AUTORIZACAO',
    observacao TEXT NULL,
    criado_por INT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_periodo_criador FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    KEY idx_periodo_datas (data_inicial, data_final)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- SOLICITACOES (cabeçalho comum a troca e passagem)
-- ---------------------------------------------------------------------
CREATE TABLE solicitacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,          -- TRC-2026-000001 / PAS-2026-000001
    tipo ENUM('TROCA','PASSAGEM') NOT NULL,
    solicitante_id INT NOT NULL,
    periodo_id INT NOT NULL,                     -- período de referência (data do plantão do solicitante)
    status VARCHAR(30) NOT NULL DEFAULT 'PENDENTE_ACEITE',
    motivo VARCHAR(255) NULL,
    observacao TEXT NULL,
    possui_excecao TINYINT(1) NOT NULL DEFAULT 0,
    excecao_id INT NULL,
    autorizado_entre_periodos TINYINT(1) NOT NULL DEFAULT 0,
    flit_status ENUM('NAO_SE_APLICA','PENDENTE_FLIT','LANCADA_FLIT') NOT NULL DEFAULT 'NAO_SE_APLICA',
    flit_lancado_por INT NULL,
    flit_lancado_em DATETIME NULL,
    motivo_reprovacao VARCHAR(255) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sol_solicitante FOREIGN KEY (solicitante_id) REFERENCES usuarios(id),
    CONSTRAINT fk_sol_periodo FOREIGN KEY (periodo_id) REFERENCES periodos_controle(id),
    CONSTRAINT fk_sol_flit_usuario FOREIGN KEY (flit_lancado_por) REFERENCES usuarios(id),
    KEY idx_sol_status (status),
    KEY idx_sol_tipo (tipo),
    KEY idx_sol_solicitante (solicitante_id),
    KEY idx_sol_periodo (periodo_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TROCAS (detalhe quando solicitacoes.tipo = TROCA)
-- ---------------------------------------------------------------------
CREATE TABLE trocas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT NOT NULL UNIQUE,
    meu_data DATE NOT NULL,
    meu_turno ENUM('SD','SN') NOT NULL,
    outro_usuario_id INT NOT NULL,
    outro_data DATE NOT NULL,
    outro_turno ENUM('SD','SN') NOT NULL,
    periodo_outro_id INT NULL,                  -- período referente à data do outro colaborador (pode diferir)
    CONSTRAINT fk_troca_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE,
    CONSTRAINT fk_troca_outro_usuario FOREIGN KEY (outro_usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_troca_periodo_outro FOREIGN KEY (periodo_outro_id) REFERENCES periodos_controle(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- PASSAGENS (detalhe quando solicitacoes.tipo = PASSAGEM)
-- ---------------------------------------------------------------------
CREATE TABLE passagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT NOT NULL UNIQUE,
    quem_passou_id INT NOT NULL,
    quem_recebeu_id INT NOT NULL,
    data DATE NOT NULL,
    hora_inicial TIME NOT NULL,
    hora_final TIME NOT NULL,
    turno ENUM('SD','SN') NOT NULL,
    CONSTRAINT fk_passagem_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE,
    CONSTRAINT fk_passagem_quem_passou FOREIGN KEY (quem_passou_id) REFERENCES usuarios(id),
    CONSTRAINT fk_passagem_quem_recebeu FOREIGN KEY (quem_recebeu_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- EXCECOES DE LIMITE
-- ---------------------------------------------------------------------
CREATE TABLE excecoes_limite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,          -- EXC-2026-000001
    usuario_id INT NOT NULL,
    periodo_id INT NOT NULL,
    setor_id INT NOT NULL,
    tipo ENUM('TROCA','PASSAGEM') NOT NULL,
    quantidade_extra INT NOT NULL DEFAULT 1,
    justificativa TEXT NOT NULL,
    solicitacao_origem_id INT NULL,              -- solicitação que motivou o pedido de exceção
    status ENUM('PENDENTE','AUTORIZADA','NEGADA','CANCELADA') NOT NULL DEFAULT 'PENDENTE',
    autorizado_por INT NULL,
    autorizado_em DATETIME NULL,
    motivo_negativa VARCHAR(255) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_exc_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_exc_periodo FOREIGN KEY (periodo_id) REFERENCES periodos_controle(id),
    CONSTRAINT fk_exc_setor FOREIGN KEY (setor_id) REFERENCES setores(id),
    CONSTRAINT fk_exc_autorizador FOREIGN KEY (autorizado_por) REFERENCES usuarios(id),
    CONSTRAINT fk_exc_solicitacao_origem FOREIGN KEY (solicitacao_origem_id) REFERENCES solicitacoes(id),
    KEY idx_exc_usuario_periodo_tipo (usuario_id, periodo_id, tipo)
) ENGINE=InnoDB;

ALTER TABLE solicitacoes
    ADD CONSTRAINT fk_sol_excecao FOREIGN KEY (excecao_id) REFERENCES excecoes_limite(id);

-- ---------------------------------------------------------------------
-- HISTORICO DAS SOLICITACOES (linha do tempo)
-- ---------------------------------------------------------------------
CREATE TABLE historico_solicitacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT NOT NULL,
    usuario_id INT NULL,
    acao VARCHAR(150) NOT NULL,
    status_anterior VARCHAR(30) NULL,
    status_novo VARCHAR(30) NULL,
    observacao TEXT NULL,
    ip VARCHAR(45) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hist_solicitacao FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE,
    CONSTRAINT fk_hist_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    KEY idx_hist_solicitacao (solicitacao_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- NOTIFICACOES
-- ---------------------------------------------------------------------
CREATE TABLE notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mensagem VARCHAR(255) NOT NULL,
    link VARCHAR(255) NULL,
    lida TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    KEY idx_notif_usuario_lida (usuario_id, lida)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- LOGS DE AUDITORIA
-- ---------------------------------------------------------------------
CREATE TABLE logs_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    acao VARCHAR(100) NOT NULL,
    entidade VARCHAR(60) NOT NULL,
    entidade_id INT NULL,
    dados_anteriores TEXT NULL,
    dados_novos TEXT NULL,
    ip VARCHAR(45) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    KEY idx_log_entidade (entidade, entidade_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- CONFIGURACOES (chave/valor)
-- ---------------------------------------------------------------------
CREATE TABLE configuracoes (
    chave VARCHAR(80) PRIMARY KEY,
    valor VARCHAR(255) NOT NULL,
    descricao VARCHAR(255) NULL,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
