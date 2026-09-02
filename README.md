# Sistema de Solicitação de Troca e Passagem de Plantão

Sistema web interno para digitalizar o fluxo de **troca** e **passagem** de
plantão, eliminando o papel e entregando ao coordenador informações
confiáveis e organizadas para lançamento manual no **FLIT**.

> ⚠️ Este sistema **não substitui o FLIT**. Ele controla apenas: solicitação,
> aceite, aprovação, limites, exceções, histórico e apoio ao lançamento no
> FLIT.

---

## 1. Tecnologias

- PHP 8.2+ (nativo, sem framework)
- MySQL / MariaDB (via PDO + prepared statements)
- Bootstrap 5 (CDN)
- JavaScript puro + Fetch API
- Sessões nativas do PHP
- Sem Node.js, sem npm, sem build

---

## 2. Estrutura de pastas

```
/app
  /Controllers     Controladores (autenticação, etc.)
  /Core            Núcleo: Database, Session, Auth, Csrf, bootstrap
  /Helpers         Funções auxiliares e constantes de status
  /Models          (repositórios cobrem esta camada — ver /Repositories)
  /Repositories     Acesso a dados (PDO) por entidade
  /Services        Regras de negócio (LimiteService, fluxo de aprovação, etc.)
/config
  app.php          Configurações gerais
  database.php     Configuração de conexão com o banco
/database
  schema.sql       Estrutura completa do banco
  seed.sql         Dados iniciais (perfis, setores, usuários de teste)
/public            Raiz pública (aponte o Apache/XAMPP para cá, ou use a raiz do projeto)
  index.php, login.php, logout.php, ...cada funcionalidade em um arquivo
  /assets/css, /assets/js
/views             Templates HTML/PHP das páginas
/storage/logs      Logs de erro do PHP
```

---

## 3. Instalação no XAMPP (ambiente local)

1. Copie a pasta do projeto para:
   ```
   C:\xampp\htdocs\troca-plantao
   ```
2. Inicie o **Apache** e o **MySQL** no painel do XAMPP.
3. Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`) e importe, **nesta
   ordem**:
   1. `database/schema.sql`
   2. `database/seed.sql`

   Ou via terminal:
   ```
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/seed.sql
   ```
4. Edite `config/database.php` se seu usuário/senha do MySQL forem
   diferentes do padrão do XAMPP (`root` / senha vazia).
5. Acesse no navegador:
   ```
   http://localhost/troca-plantao/public/
   ```
   (Se preferir esconder `/public` na URL, aponte o `DocumentRoot` do Apache
   diretamente para a pasta `public/` do projeto — veja seção 5.)

### Usuários de teste (seed.sql)

| Perfil        | Matrícula | Senha      |
|---------------|-----------|------------|
| Administrador | 0001      | admin123   |
| Coordenador   | 0002      | coord123   |
| Colaborador   | 1001      | colab123   |
| Colaborador   | 1002      | colab123   |

> As senhas são armazenadas com `password_hash()` (bcrypt) e verificadas com
> `password_verify()`. Altere-as em produção.

---

## 4. Hospedagem no servidor da instituição

O sistema é 100% PHP + MySQL, portanto funciona em qualquer hospedagem
compatível com PHP 8.2+ e MySQL/MariaDB:

1. Envie todos os arquivos do projeto para o servidor.
2. Configure o `DocumentRoot` (ou vhost) apontando para a pasta `public/`
   — assim as pastas `app/`, `config/`, `database/`, `storage/` e `views/`
   ficam fora da área acessível publicamente (mais seguro).
3. Caso não seja possível alterar o `DocumentRoot` (hospedagens
   compartilhadas simples), os arquivos `.htaccess` incluídos em `app/`,
   `config/`, `database/`, `storage/` e `views/` já bloqueiam o acesso
   direto a esses diretórios via Apache.
4. Configure `config/database.php` com as credenciais do banco do servidor.
5. Importe `schema.sql` e depois `seed.sql` (ou apenas `schema.sql` e
   cadastre os usuários reais pelo painel de administração).
6. Ajuste `config/app.php` conforme necessário (timezone, nome do sistema,
   tempo de sessão, tentativas de login).
7. Use sempre HTTPS em produção — os cookies de sessão são marcados como
   `secure` automaticamente quando o servidor informa `HTTPS=on`.

---

## 5. Configurar o Apache para servir a partir de `public/` (recomendado)

Exemplo de VirtualHost:

```apache
<VirtualHost *:80>
    ServerName troca-plantao.instituicao.local
    DocumentRoot "C:/xampp/htdocs/troca-plantao/public"

    <Directory "C:/xampp/htdocs/troca-plantao/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Com essa configuração, acesse diretamente `http://troca-plantao.instituicao.local/`.

---

## 6. Conceitos-chave do sistema

### 6.1 Regra de limites (LimiteService)

```
LIMITE EFETIVO = LIMITE PADRÃO DO PERÍODO + EXCEÇÕES AUTORIZADAS
DISPONÍVEL     = LIMITE EFETIVO − SOLICITAÇÕES UTILIZADAS/RESERVADAS
```

- Trocas e passagens têm limites **independentes**.
- O período de uma solicitação é definido pela **data do plantão**, não
  pela data de criação da solicitação.
- Status que **reservam** limite: `PENDENTE_ACEITE`, `ACEITA`,
  `AGUARDANDO_COORDENADOR`, `APROVADA`, `PENDENTE_FLIT`, `LANCADA_FLIT`.
- Status que **liberam** a vaga automaticamente: `RECUSADA`, `REPROVADA`,
  `CANCELADA`.
- Uma exceção autorizada nunca altera o limite geral do período — ela soma
  apenas ao limite efetivo do colaborador específico.

### 6.2 Fluxo de status

```
PENDENTE_ACEITE → ACEITA/RECUSADA → AGUARDANDO_COORDENADOR
                → APROVADA/REPROVADA → PENDENTE_FLIT → LANCADA_FLIT
(CANCELADA pode ocorrer a qualquer momento antes da aprovação, pelo próprio solicitante)
```

Todas as transições são validadas no **backend** (`FluxoAprovacaoService`),
independentemente do que o formulário já validou no navegador.

### 6.3 Perfis de acesso

- **Colaborador**: solicita trocas/passagens, aceita/recusa solicitações
  recebidas, acompanha limites e histórico.
- **Coordenador**: aprova/reprova, autoriza exceções, marca lançamentos no
  FLIT, gera relatórios — restrito aos setores sob sua responsabilidade.
- **RH/Administrador**: cadastra usuários, setores, períodos, limites, vê
  tudo, gera relatórios e consulta auditoria.

### 6.4 Segurança implementada

- PDO com prepared statements em 100% das consultas.
- CSRF token em todos os formulários POST.
- `password_hash()` / `password_verify()`.
- `session_regenerate_id()` no login.
- Controle de acesso por perfil e por setor (coordenador não acessa
  solicitações de setores fora de sua responsabilidade; colaborador não
  visualiza solicitações de terceiros alterando o ID na URL).
- Rate limit básico de login (bloqueio temporário após tentativas
  seguidas incorretas, configurável em `config/app.php`).
- `htmlspecialchars()` em toda saída dinâmica (proteção XSS).
- Auditoria de ações críticas (login, criação/edição de usuários, setores,
  períodos, aprovação/reprovação, exceções, lançamento no FLIT).

---

## 7. Roteiro de testes sugerido

Use os usuários de teste do `seed.sql` para validar o fluxo completo:

1. Login como colaborador (1001) → solicitar troca com o colaborador 1002.
2. Login como 1002 → aceitar a troca (ou recusar e verificar que a vaga do
   limite é liberada).
3. Login como coordenador (0002) → aprovar a solicitação.
4. Ainda como coordenador → em **Pendências FLIT**, marcar como lançada.
5. Repita solicitações até atingir o limite de 2 trocas do colaborador 1001
   no período ativo e confirme que a 3ª solicitação é bloqueada com a opção
   de **solicitar autorização excepcional**.
6. Como coordenador, autorize a exceção em **Exceções** e confirme que a
   nova solicitação passa a ser aceita e sinalizada como "Exceção
   autorizada" em toda a interface (dashboard, aprovação, relatórios).
7. Como administrador, edite o limite do próximo período em **Períodos** e
   confirme que solicitações antigas mantêm o cálculo histórico (o
   `periodo_id` é gravado na solicitação no momento da criação).
8. Verifique os relatórios (FLIT, individual, por período, exceções) e a
   tela de **Auditoria**.

---

## 8. Observações finais

- O sistema **não** gera escalas, não controla ponto/frequência e não
  substitui o FLIT — ele existe apenas para organizar a solicitação,
  aprovação e apoio ao lançamento manual no FLIT, eliminando o papel.
- Toda regra crítica (limites, transições de status, autorizações,
  controle de acesso por setor) é validada no **backend**, nunca apenas no
  frontend.
