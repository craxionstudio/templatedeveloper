<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="danger">
        <x-slot name="heading">Nomor WhatsApp belum diisi</x-slot>

        Semua tombol WhatsApp di website (detail cluster, promo, survey, tombol melayang, menu Kontak) membuka
        wa.me tanpa nomor tujuan, jadi pengunjung harus memilih kontak sendiri.
        <x-filament::link :href="$settingsUrl">Isi nomor di Pengaturan Umum</x-filament::link>
    </x-filament::section>
</x-filament-widgets::widget>
