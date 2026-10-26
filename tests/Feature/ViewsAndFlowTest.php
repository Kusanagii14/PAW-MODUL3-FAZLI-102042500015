<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ViewsAndFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Perpustakaan',
            'email' => 'admin@perpustakaan.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    public function test_buku_create_view_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('buku.create'));
        $response->assertStatus(200);
        $response->assertSee('Tambah Buku Baru');
        $response->assertSee('name="judul"', false);
        $response->assertSee('name="penulis"', false);
        $response->assertSee('name="penerbit"', false);
        $response->assertSee('name="tahun_terbit"', false);
        $response->assertSee('name="stok"', false);
    }

    public function test_peminjaman_create_view_renders_with_options(): void
    {
        $anggota = Anggota::create([
            'nama' => 'Ahmad Pratama',
            'email' => 'ahmad@example.com',
            'no_hp' => '081234567801',
        ]);

        $buku = Buku::create([
            'judul' => 'Pemrograman Laravel',
            'penulis' => 'Taylor Otwell',
            'penerbit' => 'Informatika',
            'tahun_terbit' => 2023,
            'stok' => 10,
        ]);

        $response = $this->actingAs($this->admin)->get(route('peminjaman.create'));
        $response->assertStatus(200);
        $response->assertSee('Transaksi Peminjaman Buku');
        $response->assertSee('Ahmad Pratama');
        $response->assertSee('Pemrograman Laravel');
        $response->assertSee('name="tanggal_pinjam"', false);
    }

    public function test_peminjaman_index_view_renders_empty_and_with_data(): void
    {
        // Test empty state
        $response = $this->actingAs($this->admin)->get(route('peminjaman.index'));
        $response->assertStatus(200);
        $response->assertSee('Belum ada data peminjaman buku.');

        // Add transaction
        $anggota = Anggota::create([
            'nama' => 'Ahmad Pratama',
            'email' => 'ahmad@example.com',
            'no_hp' => '081234567801',
        ]);

        $buku = Buku::create([
            'judul' => 'Pemrograman Laravel',
            'penulis' => 'Taylor Otwell',
            'penerbit' => 'Informatika',
            'tahun_terbit' => 2023,
            'stok' => 10,
        ]);

        $peminjaman = Peminjaman::create([
            'anggota_id' => $anggota->id,
            'buku_id' => $buku->id,
            'tanggal_pinjam' => now()->toDateString(),
            'status' => 'Dipinjam',
        ]);

        $response = $this->actingAs($this->admin)->get(route('peminjaman.index'));
        $response->assertStatus(200);
        $response->assertSee('Ahmad Pratama');
        $response->assertSee('Pemrograman Laravel');
        $response->assertSee('Dipinjam');
    }

    public function test_dashboard_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard Perpustakaan');
        $response->assertSee('Total Koleksi Buku');
        $response->assertSee('Total Anggota');
    }
}
