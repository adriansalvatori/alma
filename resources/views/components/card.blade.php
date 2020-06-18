<a href="@permalink">
  <div class="card has-margin-bottom-30 has-shadow-overlay has-text-white has-padding-top-0 has-padding-left-40 has-padding-right-40"
    style="background-image:url(@thumbnail('full', false));">
    <div class="card-content content is-small has-text-weight-bold">
      <div class="columns is-gapless has-margin-top-145 is-mobile is-multiline">
        <div class="column is-8">
          <div class="level-left">@title</div>
        </div>
        <div class="column is-4">
          <div class="level-right">@field('precio')</div>
        </div>
      </div>
    </div>
    <div class="card-footer is-borderless is-uppercase is-size-7">
      <div class="card-footer-item is-flex justify-space-around is-borderless has-background-dark">
        <span class="icon is-small"> <i data-feather="briefcase"></i> </span><span>@field('tipo_de_transaccion')</span>
      </div>
      <div class="card-footer-item is-flex justify-space-around is-borderless has-background-primary">
        <span class="icon is-small"> <i data-feather="maximize"></i> </span><span>@field('area_total')</span>
      </div>
      @if(get_field('parqueadero') || get_field('banos'))
      <div class="card-footer-item is-flex justify-space-around  is-borderless has-background-light has-text-dark">
        @hasfield('parqueadero')
        <span class="icon is-small"><svg xmlns="http://www.w3.org/2000/svg" width="9.91" height="7.433" viewBox="0 0 9.91 7.433">
            <path id="Icon_awesome-car" data-name="Icon awesome-car" d="M9.678,6.668H8.519L8.2,5.863A2.157,2.157,0,0,0,6.184,4.5H3.726A2.158,2.158,0,0,0,1.713,5.863l-.322.805H.233a.232.232,0,0,0-.225.289l.116.465A.232.232,0,0,0,.349,7.6H.737a1.23,1.23,0,0,0-.427.929v.929a1.229,1.229,0,0,0,.31.812v1.047a.619.619,0,0,0,.619.619h.619a.619.619,0,0,0,.619-.619v-.619H7.433v.619a.619.619,0,0,0,.619.619h.619a.619.619,0,0,0,.619-.619V10.267a1.228,1.228,0,0,0,.31-.812V8.526A1.231,1.231,0,0,0,9.173,7.6h.388a.232.232,0,0,0,.225-.176L9.9,6.956a.232.232,0,0,0-.225-.289ZM2.863,6.323a.929.929,0,0,1,.863-.584H6.184a.929.929,0,0,1,.863.584l.386.964H2.478ZM1.858,9.451a.617.617,0,1,1,0-1.235,1.192,1.192,0,0,1,.929.926C2.787,9.513,2.23,9.451,1.858,9.451Zm6.194,0c-.372,0-.929.062-.929-.309a1.192,1.192,0,0,1,.929-.926.617.617,0,1,1,0,1.235Z" transform="translate(0 -4.5)" fill="#23374d"/>
          </svg>
        </span>
        <span>@field('parqueadero')</span>
        @endfield

        @hasfield('banos')
        <span class="icon is-small">
            <svg xmlns="http://www.w3.org/2000/svg" width="7.8" height="10.4" viewBox="0 0 7.8 10.4">
                <path id="Icon_awesome-toilet" data-name="Icon awesome-toilet" d="M7.475.975A.326.326,0,0,0,7.8.65V.325A.326.326,0,0,0,7.475,0H.325A.326.326,0,0,0,0,.325V.65A.326.326,0,0,0,.325.975H.65V4.158c-.41.205-.65.451-.65.717a3.9,3.9,0,0,0,1.763,3.26L1.328,9.561a.649.649,0,0,0,.622.839h3.9a.649.649,0,0,0,.622-.839L6.037,8.135A3.9,3.9,0,0,0,7.8,4.875c0-.266-.24-.512-.65-.717V.975Zm-5.85.488A.163.163,0,0,1,1.788,1.3h.975a.163.163,0,0,1,.163.163v.325a.163.163,0,0,1-.163.163H1.788a.163.163,0,0,1-.163-.163ZM3.9,5.525c-1.566,0-2.836-.29-2.836-.65s1.27-.65,2.836-.65,2.836.29,2.836.65-1.27.65-2.836.65Z" fill="#23374d"/>
              </svg>              
        </span>
        <span>@field('banos')</span>
        @endfield
      </div>
      @endif
      <div class="card-footer-item is-borderless">
      </div>
    </div>
  </div>
</a>
