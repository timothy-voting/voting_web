<?php

namespace App\Http\Controllers;

use App\Models\VotingRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Common extends Controller
{
    const modelMapping = [
        'rule' => VotingRule::class
    ];

    public string $modelClass;
    public Model $modelInstance;

    public function __construct(Request $request){
        if(strlen($request->path())>1) {
            $this->modelClass = self::modelMapping[explode('/', str_replace('api/', '', $request->path()))[0]];
            $this->modelInstance = new $this->modelClass;
        }
    }


    /**
     * Display the specified resource.
     * @param Request $request
     * @param string $val
     * @return Response
     */
    public function show(Request $request,string $val): Response
    {
        return Response($this->modelClass::select($this->modelInstance->showColumns??$this->modelInstance->selectColumns)->where($this->modelInstance->showModel,$val)->get());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Response($this->modelClass::all($this->modelInstance->indexColumns??$this->modelInstance->selectColumns));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $records = $request->input('records');
        $responses = [];
        foreach ($records as $id=>$record){
            $instance = $this->modelClass::find($id);
            $record['b'] = $request->user()->branch_id;
            if($instance===null){
                return Response(['record'=>["Record with id $id does not exist! Refresh"]]);
            }

            $validations = [];
            $custom_validations = [];
            foreach ($record as $key=>$value){
                if(in_array($key, $this->update_keys, true)){
                    $this->in_update($key,$record,$instance,$custom_validations);
                    if(array_key_exists($key,$this->validations)) {
                        $validations[$key] = $this->validations[$key];
                    }
                }
            }

            $validator = Validator::make($record, $validations);

            if ($validator->fails()) {
                $responses[$id] = ['saved'=>false, 'reasons'=>$validator->errors()];
                continue;
            }

            if (count($custom_validations)>0) {
                return Response($custom_validations);
            }

            $instance['user_'] = $request->user()->id;
            $response = $instance->save();

            $responses[$id] = ['saved'=>$response];
        }
        return Response($responses);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $ids = $request->input('ids');
        $responses = [];
        foreach ($ids as $id){
            $inst = $this->modelClass::find($id);
//            $resp = 0;
//            if ($inst) {
//                try {
//                    $inst->user_d = $request->user()->id;
//                    $inst->save();
//                    $resp = $inst->delete();
//                } catch (QueryException $e) {
//                    $inst->user_d = null;
//                    $inst->save();
//                    $resp = -1;
//                }
//            }
            $responses[] = ['id'=>$id, 'resp'=>true];
        }

        return Response($responses);
    }

    public function keep(Request $request,array $record,\Illuminate\Contracts\Validation\Validator $validator, int $count): array
    {
        return [];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $records = $request->input('records');
        $count = 0;
        foreach ($records as $record) {
            $validator = Validator::make($record, $this->validations);

            if ($validator->fails()) {
                $validator->errors()->add('row', $count);
                return Response($validator->errors());
            }

            $arr = $this->keep($request,$record,$validator,$count);
            if($arr[0]===1){
                return Response($arr[1]);
            }

            $modelObj = new $this->modelClass($arr[1]);
            if (config('database.default')==='sqlite') {
                $modelObj->setAttribute('id',0);
                if($modelObj->isFillable('provisional')) {
                    $modelObj->setAttribute('provisional', Str::uuid()->toString());
                }
            }
            $modelObj->save();
            $count++;
        }
        return Response(0);
    }
}
