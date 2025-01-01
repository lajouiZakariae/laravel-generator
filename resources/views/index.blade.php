<x-laravel-generator::base-layout>

    <div class="p-2">

        <form class="form" @submit.prevent="post">

            <button type="button" class="btn btn-sm btn-primary mb-2" @click="addEmptyColumn">
                Add Column
            </button>

            <table>

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Primary</th>
                        <th>Nullable</th>
                        <th>Foreign</th>
                    </tr>
                </thead>

                <tbody>
                    <template v-for="column in columns" :key="column.id">
                        <tr class="form-group--column d-flex" id="column.id">
                            <td>
                                <x-laravel-generator::input placeholder="Column Name" v-model="column.name"
                                    ::id="column.name" />
                            </td>

                            <td>
                                <x-laravel-generator::input placeholder="Type" v-model="column.type" />
                            </td>

                            <td>
                                <x-laravel-generator::checkbox v-model="column.isPrimary" />
                            </td>

                            <td>
                                <x-laravel-generator::checkbox v-model="column.isNullable" />
                            </td>

                            <td>
                                <x-laravel-generator::checkbox v-model="column.isForeign" />
                            </td>
                        </tr>

                        <tr v-show="column.isForeign">
                            <td colspan="1"></td>

                            <td>References</td>

                            <td>On</td>
                        </tr>

                        <tr v-show="column.isForeign && column.foreign">
                            <td colspan="1"></td>

                            <td>
                                <x-laravel-generator::input placeholder="Type" v-model="column.foreign.references" />
                            </td>

                            <td>
                                <x-laravel-generator::input placeholder="Type" v-model="column.foreign.on" />
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary">Submit</button>

        </form>

    </div>

    <script src="https://unpkg.com/petite-vue"></script>
    <script src="{{ asset('vendor/laravel-generator/index.js') }}" type="module"></script>
</x-laravel-generator::base-layout>
