import './bootstrap';

import Alpine from 'alpinejs';
import { Livewire, Alpine as LivewireAlpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Register Alpine with Livewire
Livewire.start();

// Initialize Alpine
window.Alpine = Alpine;
Alpine.plugin(LivewireAlpine);
Alpine.start();
