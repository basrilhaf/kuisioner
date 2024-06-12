<?php

namespace App\Http\Controllers;

use App\Services\DataService;
use Crypt;
use App\Models\Pertanyaan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class PertanyaanController extends Controller
{
    protected $dataService;

    public function __construct(DataService $dataService, Request $request)
    {

        $this->middleware(function ($request, $next) {
            if ($request->session()->get('id_user') == '') {
                return Redirect::to('/login')->send();
            }

            return $next($request);
        });

        $this->dataService = $dataService;
    }

    public function index(): View
    {
        $menu_aktif = '/pertanyaan||/task';
        $navbar = $this->dataService->getMenuHTML($menu_aktif, Session::getFacadeRoot());
        $data = [
            'menu' => 'Pertanyaan Survey',
            'menu_aktif' => $menu_aktif,
            'navbar' => $navbar,
            'breadcrumb' => '<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7">
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1"><a href="#" class="text-gray-500 text-hover-primary"><i class="ki-duotone ki-home fs-6 text-gray-500 me-n1"></i></a></li>
                                <li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li><li class="breadcrumb-item text-gray-700 fw-bold lh-1">Survey</li><li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li>
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">Pertanyaan</li>
                            </ul>'
        ];
        //get posts

        //render view with posts
        return view('pertanyaan.index', $data);
    }

    public function getPertanyaan(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('t_pertanyaan')
                ->select('t_pertanyaan.*');

            if ($request->filled('pertanyaan')) {
                $query->where('t_pertanyaan.pertanyaan', 'like', '%' . $request->input('pertanyaan') . '%');
            }
            $filteredData = $query->latest()->get();

            return DataTables::of($filteredData)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $id_hash = Crypt::encrypt($row->id_pertanyaan);

                    $infoUrl = route('pertanyaan.infoPertanyaan', $id_hash);
                    $editUrl = route('pertanyaan.editPertanyaan', $id_hash);
                    $btn = '<a href=' . $infoUrl . ' class="btn btn-light-success btn-sm"><span class="fa fa-info"></span></a> ';
                    $btn .= '<a href=' . $editUrl . ' class="btn btn-light-warning btn-sm"><span class="fa fa-pencil"></span></a> ';
                    $btn .= '<button title="HAPUS" class="btn btn-danger btn-delete-pertanyaan btn-sm" data-id="' . $id_hash . '"><span class="fa fa-trash"></span></button>';
                    return $btn;
                })

                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function addPertanyaan(Request $request)
    {
        $menu_aktif = '/pertanyaan||/task';
        $navbar = $this->dataService->getMenuHTML($menu_aktif, Session::getFacadeRoot());

        $data = [
            'menu' => 'Tambah Pertanyaan Survey',
            'menu_aktif' => $menu_aktif,
            'navbar' => $navbar,
            'breadcrumb' => '<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7">
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1"><a href="#" class="text-gray-500 text-hover-primary"><i class="ki-duotone ki-home fs-6 text-gray-500 me-n1"></i></a></li>
                                <li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li><li class="breadcrumb-item text-gray-700 fw-bold lh-1">Survey</li><li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li>
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">Pertanyaan</li><li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li>
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">Tambah Pertanyaan</li>
                            </ul>'
        ];

        return view('pertanyaan.add-pertanyaan', $data);
    }

    public function editPertanyaan($id_pertanyaan, Request $request)
    {
        $menu_aktif = '/pertanyaan||/task';
        $navbar = $this->dataService->getMenuHTML($menu_aktif, Session::getFacadeRoot());
        $data = [
            'menu' => 'Edit Pertanyaan Survey',
            'menu_aktif' => $menu_aktif,
            'navbar' => $navbar,
            'breadcrumb' => '<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7">
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1"><a href="#" class="text-gray-500 text-hover-primary"><i class="ki-duotone ki-home fs-6 text-gray-500 me-n1"></i></a></li>
                                <li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li><li class="breadcrumb-item text-gray-700 fw-bold lh-1">Survey</li><li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li>
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">Pertanyaan</li><li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li>
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">Edit Pertanyaan</li>
                            </ul>',
            'id_pertanyaan' => $id_pertanyaan
        ];

        return view('pertanyaan.edit-pertanyaan', $data);
    }

    public function showDetailPertanyaan($id_pertanyaan)
    {
        $id_pertanyaan = Crypt::decrypt($id_pertanyaan);
        $data = DB::table('t_pertanyaan AS A')
            ->select('A.*')
            ->where('A.id_pertanyaan', $id_pertanyaan)
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data not found'], 404);
        }

        return response()->json($data);
    }

    public function getReffJenisPertanyaan()
    {
        $jenis_pertanyaan = DB::select('SELECT * FROM reff_kolom_table 
                                        WHERE reff_kolom_table.table = "t_pertanyaan" 
                                        AND reff_kolom_table.kolom ="jenis_pertanyaan" 
                                        ORDER BY id_reff ASC');
        return response()->json($jenis_pertanyaan);
    }

    public function addPertanyaanAction(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'jenis_jawaban' => 'required|string|max:5',
            'pertanyaan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $id_pertanyaan = DB::table('t_pertanyaan')->insertGetId([
            'pertanyaan'        => $request->pertanyaan,
            'jenis_pertanyaan'  => $request->jenis_jawaban,
            'created_pertanyaan' => '1',
            'created_at' => date("Y-m-d H:i:s")
        ]);

        if ($request->jenis_jawaban != 'F') {
            if ($id_pertanyaan != '') {
                foreach ($request->pilihan_jawaban as $pilihan) {
                    $insert_pilihan_jawaban = DB::table('t_pertanyaan_pilihan')->insert([
                        'pertanyaan_id'        => $id_pertanyaan,
                        'pilihan'  => $pilihan,
                        'created_at' => date("Y-m-d H:i:s")
                    ]);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Pertanyaan saved successfully']);
    }

    public function showPilihanPertanyaan($id_pertanyaan, Request $request)
    {
        if ($request->ajax()) {


            $id_pertanyaan = Crypt::decrypt($id_pertanyaan);
            $query = DB::table('t_pertanyaan_pilihan AS A')
                ->select('A.*')->where('A.pertanyaan_id', $id_pertanyaan)->orderBy('id_pertanyaan_pilihan', 'asc');

            $filteredData = $query->latest()->get();

            return DataTables::of($filteredData)
                ->addIndexColumn()


                ->addColumn('view_detail', function ($row) {
                    return '<button title="HAPUS" class="btn btn-danger btn-delete-pilihan_pertanyaan btn-sm" data-id="' . $row->id_pertanyaan_pilihan . '"><i class="fa fa-trash"></i></button>';
                })
                ->rawColumns(['list_pilihan_field', 'view_detail'])
                ->make(true);
        }
    }

    public function deletePilihanPertanyaanAction(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id_pertanyaan_pilihan' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // $id_pertanyaan_pilihan = Crypt::decrypt($request->id_pertanyaan_pilihan);
        $deleted = DB::table('t_pertanyaan_pilihan')->where('id_pertanyaan_pilihan', $request->id_pertanyaan_pilihan)->delete();

        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'Berhasil hapus pilihan jawaban']);
        } else {
            return response()->json(['success' => false, 'message' => 'Gagal hapus pilihan jawaban']);
        }
    }

    public function deletePertanyaanAction(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id_pertanyaan' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $id_pertanyaan = Crypt::decrypt($request->id_pertanyaan);
        $deleted = DB::table('t_pertanyaan')->where('id_pertanyaan', $id_pertanyaan)->delete();

        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'Berhasil hapus pertanyaan']);
        } else {
            return response()->json(['success' => false, 'message' => 'Gagal hapus pertanyaan']);
        }
    }


    public function addPilihanPertanyaanAction(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id_pertanyaan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->id_pertanyaan != '') {
            $id_hash = Crypt::decrypt($request->id_pertanyaan);
            foreach ($request->pilihanJawaban as $pilihan) {
                $insert_pilihan_jawaban = DB::table('t_pertanyaan_pilihan')->insert([
                    'pertanyaan_id'        => $id_hash,
                    'pilihan'  => $pilihan,
                    'created_at' => date("Y-m-d H:i:s")
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Pertanyaan saved successfully']);
    }



    public function editPertanyaanAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pertanyaan' => 'required',
            'jenisJawaban' => 'required',
            'pertanyaan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $id_pertanyaan = Crypt::decrypt($request->id_pertanyaan);
        $user = DB::table('t_pertanyaan')
            ->where('id_pertanyaan', $id_pertanyaan)
            ->update([
                'pertanyaan'     => $request->pertanyaan,
                'jenis_pertanyaan'     => $request->jenisJawaban,
                'updated_at' => date("Y-m-d H:i:s")
            ]);

        if ($user) {
            return response()->json(['success' => true, 'message' => 'Berhasil update password']);
        } else {
            return response()->json(['success' => false, 'message' => 'Gagal update password']);
        }
    }

    public function infoPertanyaan($id_pertanyaan, Request $request)
    {
        $menu_aktif = '/user||/refference';
        $navbar = $this->dataService->getMenuHTML($menu_aktif, Session::getFacadeRoot());
        $data = [
            'menu' => 'Detail Pertanyaan Survey',
            'menu_aktif' => $menu_aktif,
            'navbar' => $navbar,
            'breadcrumb' => '<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7">
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1"><a href="#" class="text-gray-500 text-hover-primary"><i class="ki-duotone ki-home fs-6 text-gray-500 me-n1"></i></a></li>
                                <li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li><li class="breadcrumb-item text-gray-700 fw-bold lh-1">Survey</li><li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li>
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">Pertanyaan</li><li class="breadcrumb-item"><i class="ki-duotone ki-right fs-7 text-gray-700 mx-n1"></i></li>
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">Detail Pertanyaan</li>
                            </ul>',
            'id_pertanyaan' => $id_pertanyaan
        ];

        return view('pertanyaan.info-pertanyaan', $data);
    }
}