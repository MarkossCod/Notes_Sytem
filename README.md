# Notes System

Aplicação web para organizar notas pessoais com categorias, etiquetas, prioridade, status, Lixeira, painel de atividades e administração de usuários. O projeto usa Laravel no backend e Blade, JavaScript e CSS no frontend.

## Funcionalidades

- cadastro público e autenticação por sessão;
- senhas fortes, limitação de tentativas e bloqueio de contas inativas;
- perfis de usuário e administrador;
- criação, edição, conclusão e visualização rápida de notas;
- categorias exclusivas por usuário;
- exclusão lógica, restauração e exclusão permanente pela Lixeira;
- painel com indicadores, histórico e gráficos de 7, 30 ou 90 dias;
- administração de contas, permissões, status e redefinição de senha;
- instalação como PWA com manifesto, ícones e service worker.

## Arquitetura

| Camada | Local | Responsabilidade |
| --- | --- | --- |
| Rotas | `routes/web.php` | Separa fluxos públicos, autenticados e administrativos. |
| Controladores | `app/Http/Controllers` | Valida entradas, aplica regras e prepara as respostas. |
| Modelos | `app/Models` | Representa contas, notas, categorias, atividades e relações. |
| Serviços | `app/Services` | Registra movimentações sem acoplar auditoria aos controladores. |
| Segurança | `app/Http/Middleware` e `app/Support` | Protege rotas, permissões e política de senha. |
| Banco | `database/migrations` | Versiona a estrutura e preserva a evolução dos dados. |
| Interface | `resources/views` | Renderiza as páginas Blade e seus componentes reutilizáveis. |
| Estilos e scripts | `resources/css` e `resources/js` | Mantém identidade visual e interações globais. |

O detalhamento dos módulos, regras e arquivos está em [`docs/ARQUITETURA.md`](docs/ARQUITETURA.md).

Os anexos das notas são armazenados no disco definido por `FILESYSTEM_DISK` e não ficam expostos publicamente. Cada arquivo é aberto por uma rota autenticada que confirma o proprietário da nota. São aceitos até cinco arquivos de 10 MB por nota nos formatos PDF, documentos, planilhas, texto e imagens comuns.

Para alterar as telas com segurança, consulte também [`docs/VIEWS_BLADE.md`](docs/VIEWS_BLADE.md).

## Requisitos

- PHP 8.3 ou superior;
- Composer;
- Node.js 22 ou superior e npm;
- MySQL/MariaDB para a configuração atual de produção;
- extensões PHP PDO, PDO MySQL e Mbstring.

## Instalação local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

No Windows, `Copy-Item .env.example .env` pode substituir o comando `cp`. Configure no `.env` a URL da aplicação e as credenciais do banco antes de executar as migrações.

## Segurança

- As senhas são armazenadas por hash e nunca serializadas pelos modelos.
- Cadastro, recuperação e redefinição exigem a política central de senha forte.
- O login possui limitação de tentativas por usuário e endereço IP.
- Rotas privadas usam `note.auth`; a gestão de usuários também exige `note.admin`.
- Contas inativas não iniciam nem mantêm acesso às áreas protegidas.
- Consultas de notas e categorias são limitadas ao usuário da sessão.
- Formulários mutáveis usam token CSRF e logout por requisição POST.

Em produção, use HTTPS, `APP_DEBUG=false`, uma `APP_KEY` exclusiva e credenciais fornecidas por variáveis de ambiente. Não versionar o arquivo `.env`.

Se a hospedagem não preservar o diretório local entre implantações, configure `FILESYSTEM_DISK` para um serviço persistente, como S3, antes de habilitar anexos em produção.

## Banco de dados

As migrações devem ser executadas na ordem registrada pelo Laravel:

```bash
php artisan migrate --force
```

Notas usam exclusão lógica. Excluir pela tela de edição apenas preenche `deleted_at`; excluir na Lixeira remove o registro definitivamente. A migração de atividades cria e preenche o histórico necessário ao Painel para instalações existentes.

## Testes e qualidade

```bash
php artisan test
npm run build
```

Os testes de integração cobrem autenticação, autorização administrativa, métricas da página inicial, categorias, Painel e Lixeira.

## Produção com Docker

O `Dockerfile` instala dependências PHP e JavaScript, gera os recursos estáticos e configura o Apache para servir apenas `public`. O `docker-entrypoint.sh` aplica as migrações antes de iniciar o servidor.

Variáveis mínimas de produção:

- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL` e `APP_DEBUG`;
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD`;
- configurações de sessão e cache compatíveis com a infraestrutura escolhida.

O `render.yaml` contém apenas valores públicos. Chaves e senhas devem ser cadastradas como segredos no provedor de hospedagem.

## Manutenção

- novas regras de negócio devem permanecer nos controladores, serviços ou classes de suporte;
- novas páginas privadas devem entrar no grupo `note.auth`;
- recursos administrativos também devem usar `note.admin`;
- alterações no banco devem ser feitas por novas migrações, nunca editando uma já aplicada;
- ao mudar arquivos listados no service worker, atualize a versão de `CACHE_NAME`.
