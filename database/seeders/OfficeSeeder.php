<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfficeSeeder extends Seeder
{
    public function run(): void
{
    $offices = [
        ['name' => 'Records Office', 'code' => 'REC'], // <--- IT IS HERE
        ['name' => 'College/Board Secretary', 'code' => 'SEC'],
        ['name' => 'Executive Assistant/Director Presidential Management/ Private Secretary', 'code' => 'EAP'],
        ['name' => 'College Registrar', 'code' => 'REG'],
        ['name' => 'Director, Administrative Services/Supervising Administrative Officer', 'code' => 'ADM'],
        ['name' => 'Director, Alumni Affairs', 'code' => 'ALU'],
        ['name' => 'Director, Animal Research Center', 'code' => 'ANI'],
        ['name' => 'Director, Auxiliary Services', 'code' => 'AUX'],
        ['name' => 'Director, Digital and ICT Development', 'code' => 'ICT'],
        ['name' => 'Director, Director for Technology Transfer and Patent Unit', 'code' => 'TTP'],
        ['name' => 'Director, ETEEAP, TVET and Micro Credential Program', 'code' => 'ETE'],
        ['name' => 'Director, Extension', 'code' => 'EXT'],
        ['name' => 'Director, Finance Services', 'code' => 'FIN'],
        ['name' => 'Director, Fisheries and Aquamarine Resources Research Center', 'code' => 'FIS'],
        ['name' => 'Director, Forest Advancement and Resources Management Research Center', 'code' => 'FOR'],
        ['name' => 'Director, Gender and Development', 'code' => 'GAD'],
        ['name' => 'Director, General Services', 'code' => 'GEN'],
        ['name' => 'Director, Health and Wellness Services', 'code' => 'HWS'],
        ['name' => 'Director, Human Resource Development Services', 'code' => 'HRD'],
        ['name' => 'Director, Indigenous People Education Research Center', 'code' => 'IPE'],
        ['name' => 'Director, Institutional Planning Unit', 'code' => 'IPU'],
        ['name' => 'Director, Instruction', 'code' => 'INS'],
        ['name' => 'Director, Internal Audit Unit', 'code' => 'IAU'],
        ['name' => 'Director, Internationalization, Linkages and Partnership', 'code' => 'ILP'],
        ['name' => 'Director, Legal Services', 'code' => 'LEG'],
        ['name' => 'Director, Library Services', 'code' => 'LIB'],
        ['name' => 'Director, NSTP', 'code' => 'NST'],
        ['name' => 'Director, Physical Infrastructure Development', 'code' => 'PID'],
        ['name' => 'Director, Public Administration and Governance Policy Research Center', 'code' => 'PAG'],
        ['name' => 'Director, Publication Services', 'code' => 'PUB'],
        ['name' => 'Director, Quality Assurance', 'code' => 'QAS'],
        ['name' => 'Director, R&D Project and Facilities', 'code' => 'RDP'],
        ['name' => 'Director, Research', 'code' => 'RES'],
        ['name' => 'Director, Sentro ng Wika at Kultura (SWK)', 'code' => 'SWK'],
        ['name' => 'Director, Sports and Culture and the Arts Development', 'code' => 'SCA'],
        ['name' => 'Director, Strategic Communication and Institutional Branding', 'code' => 'SCI'],
        ['name' => 'Director Student Affairs and Guidance Services', 'code' => 'SAG'],
        ['name' => 'Director, Tobacco and Agricultural Research Center', 'code' => 'TAR'],
        ['name' => 'Director, UIP Center for Rural Health Research, Reform and Policy', 'code' => 'RHR'],
    ];

    foreach ($offices as $off) {
        \App\Models\Office::create([
            'id' => 'ISPSC-MC-' . $off['code'] . '-2026-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'office_name' => $off['name'],
        ]);
    }
}
}