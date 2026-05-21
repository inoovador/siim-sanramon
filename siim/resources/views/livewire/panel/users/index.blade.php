<?php

use function Livewire\Volt\title;
use function Livewire\Volt\state;

title('Usuarios · SIIM');

state([
    'search'     => '',
    'filterRol'  => 'todos',
    'filterState'=> 'todos',
    'perPage'    => 10,
    'page'       => 1,
    'users' => fn () => [
        ['id' => 1,  'name' => 'Admin SIIM',         'email' => 'admin@siim.local',                      'rol' => 'admin',    'active' => true,  'last_access' => 'hace 5 min',   'created' => '01/01/2025'],
        ['id' => 2,  'name' => 'Kimberly Medina P.',  'email' => 'kimberly.medina@sanramon.gob.pe',       'rol' => 'admin',    'active' => true,  'last_access' => 'hace 30 min',  'created' => '10/02/2025'],
        ['id' => 3,  'name' => 'Carlos Aguirre M.',   'email' => 'c.aguirre@sanramon.gob.pe',             'rol' => 'analyst',  'active' => true,  'last_access' => 'hace 1 h',     'created' => '15/02/2025'],
        ['id' => 4,  'name' => 'Sofía Quispe R.',     'email' => 's.quispe@sanramon.gob.pe',              'rol' => 'analyst',  'active' => true,  'last_access' => 'hace 2 h',     'created' => '15/02/2025'],
        ['id' => 5,  'name' => 'Marco Trejos L.',     'email' => 'm.trejos@sanramon.gob.pe',              'rol' => 'analyst',  'active' => true,  'last_access' => 'ayer 16:22',   'created' => '20/02/2025'],
        ['id' => 6,  'name' => 'Diana Loayza V.',     'email' => 'd.loayza@sanramon.gob.pe',              'rol' => 'analyst',  'active' => true,  'last_access' => 'ayer 14:05',   'created' => '01/03/2025'],
        ['id' => 7,  'name' => 'Pedro Salinas G.',    'email' => 'p.salinas@sanramon.gob.pe',             'rol' => 'analyst',  'active' => true,  'last_access' => 'hace 3 h',     'created' => '05/03/2025'],
        ['id' => 8,  'name' => 'Rosa Núñez F.',       'email' => 'r.nunez@sanramon.gob.pe',               'rol' => 'citizen',  'active' => true,  'last_access' => 'hace 4 h',     'created' => '10/03/2025'],
        ['id' => 9,  'name' => 'Luis Delgado H.',     'email' => 'l.delgado@sanramon.gob.pe',             'rol' => 'citizen',  'active' => true,  'last_access' => 'ayer 09:30',   'created' => '12/03/2025'],
        ['id' => 10, 'name' => 'Carmen Quispe',       'email' => 'c.quispe@sanramon.gob.pe',              'rol' => 'citizen',  'active' => true,  'last_access' => 'hace 6 h',     'created' => '15/03/2025'],
        ['id' => 11, 'name' => 'Miguel Bautista',     'email' => 'm.bautista@sanramon.gob.pe',            'rol' => 'citizen',  'active' => true,  'last_access' => 'hace 2 días',  'created' => '18/03/2025'],
        ['id' => 12, 'name' => 'José Flores',         'email' => 'j.flores@sanramon.gob.pe',              'rol' => 'citizen',  'active' => false, 'last_access' => 'hace 15 días', 'created' => '20/03/2025'],
    ],
]);

?>

<div>
    {{-- Slide-over Nuevo Usuario --}}
    <div
        x-data="{ open: false }"
        @keydown.escape.window="open = false"
    >
        {{-- Overlay --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-ink-deep/30 z-40"
            @click="open = false"
        ></div>

        {{-- Panel --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-brand-lg z-50 flex flex-col"
        >
            <div class="flex items-center justify-between px-6 py-4 border-b border-brand-canopy/10 bg-brand-mist">
                <div>
                    <h2 class="font-serif font-bold text-brand-canopy text-lg">Nuevo usuario</h2>
                    <p class="text-xs text-ink-soft mt-0.5">Invitar funcionario al sistema</p>
                </div>
                <button @click="open = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-brand-canopy/10 text-ink-soft transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-ink-soft uppercase tracking-wider mb-1.5">Nombre completo</label>
                    <input type="text" placeholder="Ej. Ana Torres García" class="w-full rounded-lg border border-brand-canopy/20 px-3 py-2.5 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 focus:border-brand-canopy/40" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-soft uppercase tracking-wider mb-1.5">Correo electrónico</label>
                    <input type="email" placeholder="usuario@sanramon.gob.pe" class="w-full rounded-lg border border-brand-canopy/20 px-3 py-2.5 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 focus:border-brand-canopy/40" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-ink-soft uppercase tracking-wider mb-2">Rol del sistema</label>
                    <div class="space-y-2.5">
                        @foreach([['val'=>'admin','label'=>'Administrador','desc'=>'Acceso total: usuarios, configuración, reportes.'],['val'=>'analyst','label'=>'Analista','desc'=>'Dashboard, comentarios, reportes. Sin acceso a configuración.'],['val'=>'citizen','label'=>'Ciudadano','desc'=>'Solo consulta de información pública.'],] as $opt)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-brand-canopy/15 cursor-pointer hover:bg-brand-mist has-[:checked]:border-brand-canopy/40 has-[:checked]:bg-brand-canopy/5 transition-colors">
                            <input type="radio" name="nuevo_rol" value="{{ $opt['val'] }}" class="mt-0.5 accent-brand-canopy" {{ $opt['val'] === 'analyst' ? 'checked' : '' }} />
                            <div>
                                <p class="text-sm font-semibold text-ink-deep">{{ $opt['label'] }}</p>
                                <p class="text-xs text-ink-soft mt-0.5">{{ $opt['desc'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-brand-mist border border-brand-canopy/10">
                    <div>
                        <p class="text-sm font-semibold text-ink-deep">Enviar invitación por email</p>
                        <p class="text-xs text-ink-soft mt-0.5">El usuario recibirá un enlace para activar su cuenta</p>
                    </div>
                    <button
                        x-data="{ on: true }"
                        @click="on = !on"
                        :class="on ? 'bg-brand-canopy' : 'bg-ink-soft/30'"
                        class="relative w-11 h-6 rounded-full transition-colors flex-shrink-0"
                    >
                        <span :class="on ? 'translate-x-5' : 'translate-x-0.5'" class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform"></span>
                    </button>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-brand-canopy/10 flex gap-3">
                <button @click="open = false" class="flex-1 px-4 py-2.5 rounded-lg border border-brand-canopy/20 text-sm font-medium text-ink-deep hover:bg-brand-mist transition-colors">Cancelar</button>
                <button class="flex-1 px-4 py-2.5 rounded-lg bg-brand-canopy text-white text-sm font-semibold hover:bg-brand-canopy/90 transition-colors">Crear usuario</button>
            </div>
        </div>

        {{-- Modal Editar Rol --}}
        <div
            x-data="{ modalOpen: false, editUser: null }"
            @open-edit-rol.window="editUser = $event.detail; modalOpen = true"
            @keydown.escape.window="modalOpen = false"
        >
            <div
                x-show="modalOpen"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-ink-deep/40 z-50 flex items-center justify-center px-4"
                @click.self="modalOpen = false"
            >
                <div
                    x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="bg-white rounded-xl shadow-brand-lg w-full max-w-md"
                >
                    <div class="px-6 py-4 border-b border-brand-canopy/10">
                        <h3 class="font-serif font-bold text-brand-canopy">Cambiar rol</h3>
                        <p class="text-xs text-ink-soft mt-0.5" x-text="editUser ? editUser.name : ''"></p>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div class="p-3 rounded-lg bg-brand-mist text-sm">
                            <span class="text-ink-soft">Rol actual:</span>
                            <span class="font-semibold text-ink-deep ml-1" x-text="editUser ? editUser.rol : ''"></span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink-soft uppercase tracking-wider mb-1.5">Nuevo rol</label>
                            <select class="w-full rounded-lg border border-brand-canopy/20 px-3 py-2.5 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30">
                                <option value="admin">Administrador</option>
                                <option value="analyst">Analista</option>
                                <option value="citizen">Ciudadano</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-ink-soft uppercase tracking-wider mb-1.5">Razón del cambio <span class="text-brand-clay">*</span></label>
                            <textarea rows="3" placeholder="Ej. Asignado como responsable del área de comunicaciones..." class="w-full rounded-lg border border-brand-canopy/20 px-3 py-2.5 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 resize-none"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-brand-canopy/10 flex gap-3">
                        <button @click="modalOpen = false" class="flex-1 px-4 py-2.5 rounded-lg border border-brand-canopy/20 text-sm font-medium text-ink-deep hover:bg-brand-mist transition-colors">Cancelar</button>
                        <button class="flex-1 px-4 py-2.5 rounded-lg bg-brand-river text-white text-sm font-semibold hover:bg-brand-river/90 transition-colors">Aplicar cambio</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-serif font-bold text-brand-canopy">Usuarios y roles</h1>
                <p class="mt-1 text-sm text-ink-soft">Funcionarios autorizados de Imagen Institucional</p>
            </div>
            <button
                @click="open = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-brand-canopy text-white text-sm font-semibold hover:bg-brand-canopy/90 shadow-brand transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Nuevo usuario
            </button>
        </div>

        {{-- KPI row --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            @foreach([
                ['label'=>'Usuarios activos',  'value'=>'11', 'icon'=>'users',   'color'=>'brand-canopy'],
                ['label'=>'Administradores',   'value'=>'2',  'icon'=>'settings','color'=>'brand-river'],
                ['label'=>'Inactivos',         'value'=>'1',  'icon'=>'history', 'color'=>'brand-clay'],
            ] as $kpi)
            <div class="card flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-{{ $kpi['color'] }}/10 text-{{ $kpi['color'] }} flex items-center justify-center flex-shrink-0">
                    <x-icon :name="$kpi['icon']" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-ink-soft font-semibold">{{ $kpi['label'] }}</p>
                    <p class="text-2xl font-serif font-bold text-brand-canopy mt-0.5">{{ $kpi['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <div class="card mb-5">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-ink-soft">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Buscar por nombre o email..."
                        class="w-full pl-9 pr-4 py-2 rounded-lg border border-brand-canopy/20 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 focus:border-brand-canopy/40"
                    />
                </div>
                <select wire:model.live="filterRol" class="rounded-lg border border-brand-canopy/20 px-3 py-2 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 bg-white">
                    <option value="todos">Todos los roles</option>
                    <option value="admin">Administrador</option>
                    <option value="analyst">Analista</option>
                    <option value="citizen">Ciudadano</option>
                </select>
                <select wire:model.live="filterState" class="rounded-lg border border-brand-canopy/20 px-3 py-2 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 bg-white">
                    <option value="todos">Todos</option>
                    <option value="activos">Activos</option>
                    <option value="inactivos">Inactivos</option>
                </select>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wider text-ink-soft border-b border-brand-canopy/10 bg-brand-mist/60">
                            <th class="px-4 py-3 font-semibold w-10">#</th>
                            <th class="px-4 py-3 font-semibold">Nombre</th>
                            <th class="px-4 py-3 font-semibold">Email</th>
                            <th class="px-4 py-3 font-semibold">Rol</th>
                            <th class="px-4 py-3 font-semibold">Estado</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Último acceso</th>
                            <th class="px-4 py-3 font-semibold">Creado</th>
                            <th class="px-4 py-3 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-canopy/5">
                        @foreach($users as $u)
                        @php
                            $initials = collect(explode(' ', $u['name']))->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                            $rolConfig = match($u['rol']) {
                                'admin'    => ['label' => 'Admin',    'class' => 'bg-brand-canopy text-white'],
                                'analyst'  => ['label' => 'Analista', 'class' => 'bg-brand-river text-white'],
                                default    => ['label' => 'Ciudadano','class' => 'bg-ink-soft/15 text-ink-soft'],
                            };
                        @endphp
                        <tr class="hover:bg-brand-mist/40 transition-colors">
                            <td class="px-4 py-3 text-ink-soft text-xs">{{ $u['id'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-canopy/10 text-brand-canopy flex items-center justify-center flex-shrink-0">
                                        <span class="font-serif text-xs font-bold">{{ $initials }}</span>
                                    </div>
                                    <span class="font-medium text-ink-deep whitespace-nowrap">{{ $u['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-ink-soft">{{ $u['email'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $rolConfig['class'] }}">{{ $rolConfig['label'] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $u['active'] ? 'bg-green-500' : 'bg-ink-soft/40' }}"></span>
                                    <span class="text-xs text-ink-soft">{{ $u['active'] ? 'Activo' : 'Inactivo' }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-soft whitespace-nowrap">{{ $u['last_access'] }}</td>
                            <td class="px-4 py-3 text-xs text-ink-soft whitespace-nowrap">{{ $u['created'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        title="Editar usuario"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-ink-soft hover:bg-brand-canopy/10 hover:text-brand-canopy transition-colors"
                                    >
                                        <x-icon name="settings" class="w-3.5 h-3.5" />
                                    </button>
                                    <button
                                        title="Cambiar rol"
                                        @click="$dispatch('open-edit-rol', { name: '{{ $u['name'] }}', rol: '{{ $rolConfig['label'] }}' })"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-ink-soft hover:bg-brand-river/10 hover:text-brand-river transition-colors"
                                    >
                                        <x-icon name="users" class="w-3.5 h-3.5" />
                                    </button>
                                    <button
                                        title="{{ $u['active'] ? 'Desactivar' : 'Activar' }}"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-ink-soft {{ $u['active'] ? 'hover:bg-brand-clay/10 hover:text-brand-clay' : 'hover:bg-green-500/10 hover:text-green-600' }} transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            @if($u['active'])
                                                <path d="M18.36 6.64A9 9 0 1 1 5.64 17.36M12 2v10"/>
                                            @else
                                                <path d="M9 12l2 2 4-4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                            @endif
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-brand-canopy/10 bg-brand-mist/30">
                <div class="flex items-center gap-2 text-xs text-ink-soft">
                    Mostrando
                    <select wire:model.live="perPage" class="rounded border border-brand-canopy/20 px-2 py-1 text-xs text-ink-deep focus:outline-none focus:ring-1 focus:ring-brand-canopy/30 bg-white">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    por página · <span class="font-medium text-ink-deep">12</span> funcionarios totales
                </div>
                <div class="flex items-center gap-1">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium border border-brand-canopy/20 text-ink-soft hover:bg-brand-mist transition-colors disabled:opacity-40" disabled>Anterior</button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-canopy text-white">1</button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-medium border border-brand-canopy/20 text-ink-soft hover:bg-brand-mist transition-colors disabled:opacity-40" disabled>Siguiente</button>
                </div>
            </div>
        </div>
    </div>
</div>
