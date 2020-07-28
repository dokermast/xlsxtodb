@extends('basic')

@section('form')

<div class="text-center" style="color: blue"><h4>Form for uploading xlsx files</h4></div>

    <br>
    <br>

    <div class="container">

        <form action="{{ route('upload', [], false) }}"  method="post" enctype="multipart/form-data">

            @csrf

            {{-- FILE--}}
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-1"></div>
                    <div class="col-sm-2">
                        <label for="age">File</label>
                    </div>
                    <div class="col-sm-8">
                        <input type="file" class="form-control-file" id="file" name="file"  value="{{ old('file') }}" required>
                    </div>
                </div>
            </div>

            {{--   CHECKBOX  --}}
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-1"></div>
                    <div class="col-sm-2">
                        <label for="fix">Fix file</label>
                    </div>
                    <div class="col-sm-8">
                        <input class="" type="checkbox" name="fix" value="1" id="fix">
                        <label class="" for="fix">Check if file damaged </label>
                    </div>

                </div>
            </div>

            {{-- BUTTON --}}
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-offset-0 col-sm-8">
                        <input type="submit" class="btn btn-primary" value="Upload">
                    </div>
                </div>
            </div>

        </form>

        <br>
        <br>
        <br>

        <div class="text-center">
            <a class="btn btn-secondary" href="{{ route('trunkate', [], false) }}">Clean DB</a>
            <p>Clean DB before uploading</p>
        </div>

    </div>

@endsection
