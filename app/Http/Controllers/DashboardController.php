<?php

namespace App\Http\Controllers;

use App\Services\DataService;
use Illuminate\Support\Facades\DB;
use Crypt;
use App\Models\Menu;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;



class DashboardController extends Controller
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
        $menu_aktif = '/dashboard||/dashboard2';
        $navbar = $this->dataService->getMenuHTML($menu_aktif, Session::getFacadeRoot());
        $data = [
            'menu' => 'Dashboard',
            'menu_aktif' => $menu_aktif,
            'navbar' => $navbar,
            'breadcrumb' => '<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7">
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1"><a href="#" class="text-gray-500 text-hover-primary"><i class="ki-duotone ki-home fs-6 text-gray-500 me-n1"></i></a></li>
                                
                            </ul>'
        ];
        
        return view('dashboard.index', $data);
    }

    public function dashboardAnggota(): View
    {
        $menu_aktif = '/dashboardAnggota||/dashboard2';
        $navbar = $this->dataService->getMenuHTML($menu_aktif, Session::getFacadeRoot());
        $data = [
            'menu' => 'Dashboard Anggota',
            'menu_aktif' => $menu_aktif,
            'navbar' => $navbar,
            'breadcrumb' => '<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7">
                                <li class="breadcrumb-item text-gray-700 fw-bold lh-1"><a href="#" class="text-gray-500 text-hover-primary"><i class="ki-duotone ki-home fs-6 text-gray-500 me-n1"></i></a></li>
                                
                            </ul>'
        ];
        
        return view('dashboard.anggota', $data);
    }

    
    public function chartAnggotaBermasalah(Request $request)
    {
        
        $cabang = $request->input('cabang');
        $filterBy = $request->input('filter_by');
        $tahun = $request->input('tahun');
        $tampil = $request->input('tampil_berdasarkan');
        $tanggal = $request->input('tanggal');

        $chart_ab = DB::table('anggota_bermasalah');

        

        if ($filterBy == 'thn') {
            if ($tampil == 'min') {
                $chart_ab->select(DB::raw('CONCAT("minggu ", WEEK(tanggal_ab), "-", YEAR(tanggal_ab)) as bulan'), DB::raw('count(id_ab) as jumlah'));
            }else{
                $chart_ab->select(DB::raw('DATE_FORMAT(tanggal_ab, "%b-%Y") as bulan'), DB::raw('count(id_ab) as jumlah'));
            }
            $chart_ab->whereYear('tanggal_ab', '=', $tahun);
        }else{
            if ($tanggal !="") {
                $chart_ab->select(DB::raw('CONCAT("minggu ", WEEK(tanggal_ab), "-", YEAR(tanggal_ab)) as bulan'), DB::raw('count(id_ab) as jumlah'));
                $decoded_date_range = urldecode($tanggal);

                // Memisahkan tanggal menjadi dua bagian: awal dan akhir
                [$awal, $akhir] = explode(' to ', $decoded_date_range);

                // Menambahkan filter berdasarkan rentang tanggal
                $chart_ab->where('tanggal_ab', '>=', $awal)
                    ->where('tanggal_ab', '<=', $akhir);
            }
            
        }

        if ($cabang != '') {
            $chart_ab->where('cabang_ab', $cabang);
        }

        $chart_ab->groupBy(DB::raw('bulan'))->orderBy(DB::raw('bulan'))->get();

        // $chart_ab = DB::table('anggota_bermasalah') 
        // ->select(DB::raw('DATE_FORMAT(tanggal_ab, "%b-%Y") as bulan'), DB::raw('count(id_ab) as jumlah'))
        //         ->groupBy(DB::raw('bulan'))
        //         ->orderBy(DB::raw('bulan'))
        //         ->get();

        // Kembalikan data dalam format JSON
        return response()->json([
            'bulan' => $chart_ab->pluck('bulan'),
            'jumlah' => $chart_ab->pluck('jumlah'),
        ]);
    }

    
    public function chartKelompokTelat(Request $request)
    {
        
        $cabang = $request->input('cabang');
        $filterBy = $request->input('filter_by');
        $tahun = $request->input('tahun');
        $tampil = $request->input('tampil_berdasarkan');
        $tanggal = $request->input('tanggal');
        $chart_ab = DB::table('kelompok_bermasalah');

        // $que=$mysqli->query("SELECT YEAR(tanggal_kb) as tahun, WEEK(tanggal_kb) as bulan,DATE_SUB(tanggal_kb,INTERVAL DAYOFWEEK(tanggal_kb)-1 DAY) as bln,count(id_kb) as jumlah FROM kelompok_bermasalah WHERE kode_kb = '3A'  AND YEAR(tanggal_kb) = '$tahun' GROUP BY YEAR(tanggal_kb),WEEK(tanggal_kb) order by tanggal_kb ASC");         

        if ($filterBy == 'thn') {
            if ($tampil == 'min') {
                $chart_ab->select(DB::raw('CONCAT("minggu ", WEEK(tanggal_kb), "-", YEAR(tanggal_kb)) as bulan'), DB::raw('count(id_kb) as jumlah'));
            }else{
                $chart_ab->select(DB::raw('DATE_FORMAT(tanggal_kb, "%b-%Y") as bulan'), DB::raw('count(id_kb) as jumlah'));
            }
            $chart_ab->whereYear('tanggal_kb', '=', $tahun);
        }else{
            if ($tanggal !="") {
                $chart_ab->select(DB::raw('CONCAT("minggu ", WEEK(tanggal_kb), "-", YEAR(tanggal_kb)) as bulan'), DB::raw('count(id_kb) as jumlah'));
                $decoded_date_range = urldecode($tanggal);

                // Memisahkan tanggal menjadi dua bagian: awal dan akhir
                [$awal, $akhir] = explode(' to ', $decoded_date_range);

                // Menambahkan filter berdasarkan rentang tanggal
                $chart_ab->where('tanggal_kb', '>=', $awal)
                    ->where('tanggal_kb', '<=', $akhir);
            }
            
        }

        if ($cabang != '') {
            $chart_ab->where('cabang_kb', $cabang);
        }

        $chart_ab->where('kode_kb', '3A');
        $chart_ab->groupBy(DB::raw('bulan'))->orderBy(DB::raw('bulan'))->get();

        // Kembalikan data dalam format JSON
        return response()->json([
            'bulan' => $chart_ab->pluck('bulan'),
            'jumlah' => $chart_ab->pluck('jumlah'),
        ]);
    }

    
    public function chartKelompokBerat(Request $request)
    {
        
        $cabang = $request->input('cabang');
        $filterBy = $request->input('filter_by');
        $tahun = $request->input('tahun');
        $tampil = $request->input('tampil_berdasarkan');
        $tanggal = $request->input('tanggal');
        $chart_ab = DB::table('kelompok_bermasalah');

        // $que=$mysqli->query("SELECT YEAR(tanggal_kb) as tahun, WEEK(tanggal_kb) as bulan,DATE_SUB(tanggal_kb,INTERVAL DAYOFWEEK(tanggal_kb)-1 DAY) as bln,count(id_kb) as jumlah FROM kelompok_bermasalah WHERE kode_kb = '3A'  AND YEAR(tanggal_kb) = '$tahun' GROUP BY YEAR(tanggal_kb),WEEK(tanggal_kb) order by tanggal_kb ASC");         

        if ($filterBy == 'thn') {
            if ($tampil == 'min') {
                $chart_ab->select(DB::raw('CONCAT("minggu ", WEEK(tanggal_kb), "-", YEAR(tanggal_kb)) as bulan'), DB::raw('count(id_kb) as jumlah'));
            }else{
                $chart_ab->select(DB::raw('DATE_FORMAT(tanggal_kb, "%b-%Y") as bulan'), DB::raw('count(id_kb) as jumlah'));
            }
            $chart_ab->whereYear('tanggal_kb', '=', $tahun);
        }else{
            if ($tanggal !="") {
                $chart_ab->select(DB::raw('CONCAT("minggu ", WEEK(tanggal_kb), "-", YEAR(tanggal_kb)) as bulan'), DB::raw('count(id_kb) as jumlah'));
                $decoded_date_range = urldecode($tanggal);

                // Memisahkan tanggal menjadi dua bagian: awal dan akhir
                [$awal, $akhir] = explode(' to ', $decoded_date_range);

                // Menambahkan filter berdasarkan rentang tanggal
                $chart_ab->where('tanggal_kb', '>=', $awal)
                    ->where('tanggal_kb', '<=', $akhir);
            }
            
        }

        if ($cabang != '') {
            $chart_ab->where('cabang_kb', $cabang);
        }

        $chart_ab->where('kode_kb', '3B');
        $chart_ab->groupBy(DB::raw('bulan'))->orderBy(DB::raw('bulan'))->get();

        // Kembalikan data dalam format JSON
        return response()->json([
            'bulan' => $chart_ab->pluck('bulan'),
            'jumlah' => $chart_ab->pluck('jumlah'),
        ]);
    }
   
}
