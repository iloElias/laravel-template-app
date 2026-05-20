# Database Seeders

## Estrutura

```
database/seeders/
├── DatabaseSeeder.php          # Orquestrador principal (com lógica de ambiente)
├── Development/                # Seeders de desenvolvimento
│   └── UserSeeder.php         # Usuários de teste
└── Production/                # Seeders de produção (dados essenciais)
    └── .gitkeep
```

## Execução Automática

Os seeders são executados **automaticamente** durante o deploy via `ci.sh`:

```bash
bash /app/script/seed.sh
```

## Lógica Condicional

O `DatabaseSeeder` executa diferentes seeders baseado no `APP_ENV`:

| Ambiente                | Seeders Executados                 |
| ----------------------- | ---------------------------------- |
| `local`, `development`  | Development/\* (usuários de teste) |
| `staging`, `production` | Production/\* (dados essenciais)   |

## Como Adicionar Novo Seeder

### 1. Desenvolvimento (Dados de Teste)

```bash
php artisan make:seeder Development/TestDataSeeder
```

```php
<?php

namespace Database\Seeders\Development;

use Illuminate\Database\Seeder;
use App\Models\Hr\User;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(50)->create();
    }
}
```

Registre no `DatabaseSeeder.php`:

```php
if (app()->environment(['local', 'development'])) {
    $this->call([
        UserSeeder::class,
        TestDataSeeder::class, // ← Adicionar aqui
    ]);
}
```

### 2. Produção (Dados Essenciais)

```bash
php artisan make:seeder Production/SystemConfigSeeder
```

```php
<?php

namespace Database\Seeders\Production;

use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Apenas dados ESSENCIAIS que devem existir em produção
        // Exemplo: roles padrão, configurações de sistema
    }
}
```

Registre no `DatabaseSeeder.php`:

```php
if (app()->environment(['staging', 'production'])) {
    $this->call([
        SystemConfigSeeder::class, // ← Adicionar aqui
    ]);
}
```

## Execução Manual

```bash
# Rodar todos os seeders (respeita APP_ENV)
php artisan db:seed

# Rodar seeder específico
php artisan db:seed --class=Database\\Seeders\\Development\\UserSeeder

# Forçar em produção
php artisan db:seed --force
```

## ⚠️ Boas Práticas

### ✅ Fazer

- Usar seeders de **Development** para dados de teste
- Usar seeders de **Production** para dados essenciais (roles, configs)
- Fazer seeders **idempotentes** (não falham se dados já existem)
- Usar `updateOrCreate()` ou verificar antes de inserir

### ❌ Evitar

- Dados sensíveis em seeders (senhas reais, tokens, etc)
- Seeders que modificam dados existentes em produção
- Seeders lentos (use migrations para muitos dados)
- Hard-coded IDs (use UUIDs ou busque por chave única)

## Exemplo: Seeder Idempotente

```php
<?php

namespace Database\Seeders\Production;

use Illuminate\Database\Seeder;
use App\Models\System\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'manager', 'user'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => ucfirst($roleName) . ' role']
            );
        }

        $this->command->info('✓ Roles created/verified');
    }
}
```

## Troubleshooting

### Seeder não está rodando

Verifique:

1. `APP_ENV` está correto? (`echo $APP_ENV`)
2. Seeder está registrado no `DatabaseSeeder.php`?
3. Namespace está correto?

### Erro "Class not found"

```bash
# Recompile autoload
composer dump-autoload
```

### Dados duplicados

Use `firstOrCreate()`, `updateOrCreate()` ou verifique existência antes de inserir.
