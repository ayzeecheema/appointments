<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Image;

class CommonController extends Controller
{
    protected const clients = [
        '0' => 'A',
        '1' => 'B',
        '2' => 'C',
        '3' => 'D',
        '4' => 'E',
        '5' => 'F',
    ];

    public function appointments($request, $maxAppointments = null)
    {
        if ($request->has('schedules')) {
            $clients = array();
            $html = "";

            for ($i = 0; $i < count($request->schedules); $i++) {
                array_push($clients, self::clients[$i]);
                $html .= '<tr><td> ' . self::clients[$i] . '</td>';
                $count = count($request->schedules[$i]);
                for ($j = 0; $j < $count; $j++) {
                    $html .= '<td>' . $request->schedules[$i][$j][0] . "-" . $request->schedules[$i][$j][1] . '</td>';
                }
                if ($j < max($maxAppointments)) {
                    $additionalColumns = max($maxAppointments) - $j;
                    for ($loop = 0; $loop < $additionalColumns; $loop++) {
                        $html .= '<td>-</td>';
                    }
                }
                $html .= '</tr>';
            }
            $data['html'] = $html;
            $data['clients'] = $clients;

            return $data;
        } else {
            // return $request;
            $startTime = "09:00";
            $startTime = strtotime($startTime);
            $startTime = date('H:i', $startTime);
            $offtime = "19:00";
            $offtime = strtotime($offtime);
            $offtime = date('H:i', $offtime);
            for ($i = 0; $i < count($request->appointments); $i++) {
                if (self::clients[$i] == $request->client) {
                    $count = count($request->appointments[$i]);
                    for ($j = 0; $j < $count; $j++) {
                        ///////////////first appointment start time/////////////////////
                        $time = strtotime($request->appointments[$i][0][0]);
                        $firstAppointmentStartTime = date('H:i', $time);
                        /////////////////////////////////////////////////////////////

                        ///////////////this appointment end time/////////////////////
                        $time2 = strtotime($request->appointments[$i][$j][1]);
                        $thisAppointmentEndTime = date('H:i', $time2);
                        /////////////////////////////////////////////////////////////

                        ////////////////next appointment start time//////////////////
                        $time3 = strtotime($request->appointments[$i][$j + 1][0]);
                        $nextAppointmentStartTime = date('H:i', $time3);
                        ////////////////////////////////////////////////////////////

                        /////////////////////check if appointment available///////////////
                        $newAppointment = date('H:i', strtotime($startTime . ' +' . $request->minutes . ' minutes'));
                        if ($newAppointment <= $firstAppointmentStartTime) {
                            $response['status'] = "success";
                            $response['message'] = "Appointment Available From " . $startTime . " to " . $newAppointment . ".";
                            return $response;
                        } else {
                            $newAppointment = date('H:i', strtotime($thisAppointmentEndTime . ' +' . $request->minutes . ' minutes'));
                            if ($newAppointment <= $nextAppointmentStartTime and $newAppointment <= $offtime) {
                                $response['status'] = "success";
                                $response['message'] = "Appointment Available From " . $thisAppointmentEndTime . " to " . $newAppointment . ".";
                                return $response;
                            } else {
                                $response['status'] = "Error";
                                $response['message'] = "Appointment Not Available.";
                                return $response;
                            }
                        }
                    }
                }
            }
        }
    }

    public function dashboard(Request $request)
    {
        $maxAppointments = array();

        /////////////////for creating table of appointments and populating clients dropdown//////////////////////////////////
        if ($request->has('schedules')) {
            foreach ($request->schedules as $schedule) {
                array_push($maxAppointments, count($schedule));
            }

            $appointmentsHtml = self::appointments($request, $maxAppointments);

            $clientsDropDown = "";

            foreach ($appointmentsHtml['clients'] as $client) {
                $clientsDropDown .= '<option value="' . $client . '">' . $client . '</option>';
            }
            $response['html'] = $appointmentsHtml['html'];
            $response['clients'] = $clientsDropDown;
            return $response;
        }
        /////////////////////////////////////////////////////////////////////////////////////

        /////////////////for finding appointments///////////////////////////////////////////
        if ($request->has('appointments')) {
            return $availableAppointment = self::appointments($request);
        }
        ///////////////////////////////////////////////////////////////////////////////////

        return view('dashboard', get_defined_vars());
    }

    public function ImageUpload(Request $request)
    {
        // $image = $request->file('file');
        // $imageName = $image->getClientOriginalName();
        // $image->move(public_path('images'), $imageName);

        // return response()->json(['success' => $imageName]);
        // $image = $request->file('file');
        // $imageName = $image->getClientOriginalName();
        // $imgFile = Image::make($image->getRealPath());
        // $imgFile->text(date('y-m-d'), 120, 100, function ($font) {
        //     $font->size(35);
        //     $font->color('#ffffff');
        //     $font->align('center');
        //     $font->valign('bottom');
        //     $font->angle(90);
        // })->save(public_path('images'), $imageName);
        $currentDate = date('d-m-y');
        $file = $request->file('file');
        $file_name = time() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('/images');
        $new_img = Image::make($file->getRealPath());
        $new_img->text(
            $currentDate,
            450,
            100,
            function ($font) {
                $font->file(public_path('RobotoMono-VariableFont_wght.ttf'));
                $font->size(100);
                $font->color('#FF0000');
                $font->align('center');
                $font->valign('bottom');
            }
        );
        File::exists($destinationPath) or File::makeDirectory($destinationPath);
        $new_img->save($destinationPath . '/' . $file_name);
        return response()->json(['success' => "uploaded successfully!"]);
    }

    public function imagesList(Request $request)
    {
        $html = '';
        foreach (File::glob(public_path('images') . '/*') as $path) {
            $html .= '<div class="col-4">';
            $html .= '<img src="' . str_replace(public_path(), '', $path) . '" width="400" height="300">';
            $html .= '</div>';
        }
        return $html;
    }

    private function saveImage($image, $path)
    {

        if ($image && $path) {

            $filename = $image->getClientOriginalName();
            $destination_path = public_path($path);

            \File::isDirectory($path) or \File::makeDirectory($destination_path, 0777, true, true);

            $new_filename = Str::random(32) . '.' . $image->getClientOriginalExtension();
            $image->move($destination_path, $new_filename);

            return $new_filename;
        }

        return null;
    }
}
