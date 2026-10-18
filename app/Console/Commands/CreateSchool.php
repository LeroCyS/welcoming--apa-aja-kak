<?php

namespace App\Console\Commands;

use App\Actions\ProvisionSchoolAction;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Dipakai tim Welcoming saat sekolah baru menghubungi kami: membuat sekolah beserta akun admin sekolahnya. */
class CreateSchool extends Command
{
    protected $signature = 'welcoming:create-school
        {name : Nama sekolah, mis. "SMP Negeri 1 Makassar"}
        {level : Jenjang: sd, smp, sma, atau smk}
        {admin-email : Email akun admin sekolah}
        {--admin-name=Admin Sekolah : Nama akun admin}
        {--timezone=Asia/Makassar : Zona waktu sekolah}
        {--address= : Alamat sekolah}';

    protected $description = 'Buat sekolah baru + akun admin sekolah (password sementara, wajib diganti saat masuk pertama)';

    public function handle(ProvisionSchoolAction $provision): int
    {
        $input = [
            'name' => $this->argument('name'),
            'level' => Str::lower($this->argument('level')),
            'email' => Str::lower($this->argument('admin-email')),
            'timezone' => $this->option('timezone'),
        ];
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:150', 'unique:schools,name'],
            'level' => ['required', Rule::in(array_keys(School::LEVELS))],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'timezone' => ['required', 'timezone'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $password = Str::password(12, symbols: false);
        [$school, $admin] = DB::transaction(function () use ($input, $password, $provision) {
            $school = School::create([
                'name' => $input['name'],
                'level' => $input['level'],
                'address' => $this->option('address'),
                'timezone' => $input['timezone'],
                'default_service_start' => '07:00:00',
                'default_service_end' => '15:00:00',
            ]);
            $provision->execute($school);
            $admin = User::create([
                'school_id' => $school->id,
                'school_joined_at' => now(),
                'role' => User::ROLE_ADMIN,
                'name' => $this->option('admin-name'),
                'email' => $input['email'],
                'password' => $password,
                'must_change_password' => true,
                'is_active' => true,
            ]);
            AuditLog::record('school_created', $school, ['level' => $school->level], $admin->id);

            return [$school, $admin];
        });

        $this->info("Sekolah \"{$school->name}\" ({$school->levelLabel()}) dibuat.");
        $this->table(['Data', 'Nilai'], [
            ['Email admin', $admin->email],
            ['Password sementara', $password],
        ]);
        $this->line('Admin wajib mengganti password saat masuk pertama. Murid bisa langsung memilih sekolah ini saat onboarding.');

        return self::SUCCESS;
    }
}
