# Biblioteca Ginestal Machado

Sistema de gestão bibliotecária moderno desenvolvido em PHP e Bootstrap para a Escola Ginestal Machado.

## 🚀 Funcionalidades

### Entidades Principais
- **📚 Livros** - Gestão completa do acervo
- **👤 Autores** - Cadastro e biografia de escritores
- **🏢 Editoras** - Informações das editoras
- **📮 Códigos Postais** - Gestão de localidades
- **🏷️ Gêneros** - Categorização dos livros
- **👥 Utentes** - Usuários da biblioteca
- **📋 Requisições** - Sistema de empréstimos

### Recursos do Sistema
- ✅ Dashboard com estatísticas em tempo real
- ✅ Interface responsiva e moderna
- ✅ Busca avançada de livros
- ✅ Gestão de empréstimos e devoluções
- ✅ Controle de atrasos e multas
- ✅ Relatórios automáticos
- ✅ Sistema de notificações

## 📋 Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, PDO_MySQL

## 🛠️ Instalação

### 1. Configurar Base de Dados

1. Crie uma base de dados MySQL chamada `GM_biblioteca`
2. Execute o script SQL em `database/biblioteca_ginestal.sql`
3. Configure as credenciais em `config/database.php`:

```php
private $host = 'localhost';
private $db_name = 'GM_biblioteca';
private $username = 'seu_usuario';
private $password = 'sua_senha';
```

### 2. Configurar Servidor Web

1. Coloque os arquivos na pasta do seu servidor web
2. Certifique-se que o PHP tem permissões de escrita
3. Configure o DocumentRoot para apontar para a pasta do projeto

### 3. Testar Instalação

1. Acesse `http://localhost/GM_BIBLIOTECA/index.php`
2. Verifique se as estatísticas são carregadas corretamente
3. Teste a navegação entre as páginas

## 📁 Estrutura do Projeto

```
GM_BIBLIOTECA/
├── index.php                 # Página principal
├── config/
│   └── database.php         # Configuração da base de dados
├── database/
│   └── biblioteca_ginestal.sql # Script de criação da BD
├── pages/                   # Páginas do sistema
│   ├── livros.php          # Gestão de livros
│   ├── autores.php         # Gestão de autores
│   ├── utentes.php         # Gestão de utentes
│   ├── requisicoes.php     # Gestão de requisições
│   ├── editoras.php        # Gestão de editoras
│   ├── generos.php         # Gestão de gêneros
│   └── codigos_postais.php # Gestão de códigos postais
├── ajax/
│   └── refresh_stats.php   # Atualização de estatísticas
└── README.md               # Este arquivo
```

## 🎯 Como Usar

### Dashboard Principal
- Visualize estatísticas em tempo real
- Acesse rapidamente as principais funcionalidades
- Veja livros populares e requisições recentes

### Gestão de Livros
- Adicione novos livros ao acervo
- Busque livros por título, autor, ISBN
- Controle quantidades disponíveis

### Gestão de Utentes
- Cadastre novos utentes (alunos, professores, funcionários)
- Organize por tipo e localização
- Controle histórico de empréstimos

### Sistema de Requisições
- Registre novos empréstimos
- Controle datas de devolução
- Gerencie renovações e multas

## 🔧 Personalização

### Cores e Estilo
Edite as variáveis CSS no início do `index.php`:
```css
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;
}
```

### Configurações da Biblioteca
Modifique as configurações em `config/database.php`:
- Prazo padrão de empréstimo
- Valor de multas
- Limites por tipo de utente

## 📊 Base de Dados

### Tabelas Principais
- `livro` - Informações dos livros
- `autor` - Dados dos autores
- `editora` - Informações das editoras
- `genero` - Categorias de livros
- `utente` - Usuários da biblioteca
- `codigo_postal` - Localidades
- `requisicao` - Empréstimos

### Views Úteis
- `v_livros_disponiveis` - Livros disponíveis para empréstimo
- `v_requisicoes_ativas` - Empréstimos ativos com informações

### Triggers Automáticos
- Atualização automática de quantidades disponíveis
- Controle de status de requisições

## 🚨 Solução de Problemas

### Erro de Conexão com BD
1. Verifique as credenciais em `config/database.php`
2. Confirme se o MySQL está rodando
3. Teste a conexão manualmente

### Páginas em Branco
1. Verifique os logs de erro do PHP
2. Confirme se todas as extensões estão instaladas
3. Teste com `error_reporting(E_ALL)`

### Estatísticas Não Carregam
1. Verifique se o arquivo `ajax/refresh_stats.php` existe
2. Confirme permissões de leitura
3. Teste o endpoint diretamente no navegador

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique este README
2. Consulte os comentários no código
3. Teste em ambiente de desenvolvimento primeiro

## 📝 Licença

Sistema desenvolvido para a Escola Ginestal Machado.
Todos os direitos reservados.

---

**Desenvolvido com ❤️ para a Educação**
